<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Enum\InPostDeliveryType;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Exception\InPostPayRestrictedProductException;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use InPost\InPostPay\Provider\Delivery\DeliveryDateProvider;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use InPost\InPostPay\Service\CreateBasketNotice;
use InPost\InPostPay\Validator\DigitalQuoteValidator;
use Magento\Customer\Api\AddressRepositoryInterface;
use InPost\InPostPay\Validator\QuoteRestrictionsValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteToBasketDeliveryDataTransfer implements QuoteToBasketDataTransferInterface
{
    private const DEFAULT_COUNTRY_ID = 'PL';

    /**
     * @param DeliveryInterfaceFactory $deliveryFactory
     * @param DeliveryOptionInterfaceFactory $deliveryOptionFactory
     * @param DeliveryDateProvider $deliveryDateProvider
     * @param ShipmentMappingConfigProvider $shipmentMappingConfigProvider
     * @param AddressRepositoryInterface $addressRepository
     * @param CreateBasketNotice $createBasketNotice
     * @param QuoteRestrictionsValidator $quoteRestrictionsValidator
     * @param ShippingMethodConverter $shippingMethodConverter
     * @param DigitalQuoteValidator $digitalQuoteValidator
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly DeliveryInterfaceFactory $deliveryFactory,
        private readonly DeliveryOptionInterfaceFactory $deliveryOptionFactory,
        private readonly DeliveryDateProvider $deliveryDateProvider,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly AddressRepositoryInterface $addressRepository,
        private readonly CreateBasketNotice $createBasketNotice,
        private readonly QuoteRestrictionsValidator $quoteRestrictionsValidator,
        private readonly ShippingMethodConverter $shippingMethodConverter,
        private readonly DigitalQuoteValidator $digitalQuoteValidator,
        private readonly LoggerInterface $logger
    ) {
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $storeId = $quote->getStoreId();

        if (!$this->digitalQuoteValidator->isDigitalQuoteAllowed($quote)) {
            $this->logger->error(
                'Quote contains digital products and Magento config does not allow guest orders.'
                . ' Setting empty delivery.'
            );
            $basket->setDelivery([]);
            $this->setBasketNoticeForGuestUnavailableDigitalProducts((string)$basket->getBasketId());

            return;
        }

        try {
            $this->quoteRestrictionsValidator->validate($quote, true);
        } catch (InPostPayRestrictedProductException $e) {
            $this->logger->error(
                sprintf('Restricted product in cart. Setting empty delivery. Reason: %s', $e->getMessage())
            );
            $basket->setDelivery([]);
            return;
        }

        if ((int)$quote->getItemsCount() === 0) {
            $this->logger->error('Empty cart. Setting empty delivery.');
            $basket->setDelivery([]);
            return;
        }

        if (!$quote->isVirtual()) {
            $shippingMethods = $this->getShippingMethodsForQuote($quote);
            $deliveries = $this->prepareMappedShippingMethodsData($shippingMethods, $storeId);
            $deliveries = array_merge($deliveries, $this->prepareDigitalDeliveryData($storeId));
        } else {
            $deliveries = $this->prepareDigitalDeliveryData($storeId);
        }

        if (empty($deliveries)) {
            $this->createBasketNotice->execute(
                (string)$basket->getBasketId(),
                InPostPayBasketNoticeInterface::ATTENTION,
                __('No delivery method is allowed for this basket.')->render()
            );
        }

        $basket->setDelivery($deliveries);
    }

    /**
     * @param Quote $quote
     * @return ShippingMethodInterface[]
     */
    private function getShippingMethodsForQuote(Quote $quote): array
    {
        $output = [];
        /** @var Address $shippingAddress */
        $shippingAddress = $this->getShippingAddress($quote);
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->collectShippingRates();
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();

        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->shippingMethodConverter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }

        return $output;
    }

    /**
     * @param ShippingMethodInterface[] $quoteAvailableShippingMethods
     * @param int $storeId
     * @return array
     */
    private function prepareMappedShippingMethodsData(array $quoteAvailableShippingMethods, int $storeId): array
    {
        $deliveryData = [];
        foreach ($this->shipmentMappingConfigProvider->getAllDeliveryTypes() as $deliveryType) {
            $shippingMethod = $this->getDeliveryByTypeAndOption(
                $quoteAvailableShippingMethods,
                $deliveryType,
                ShipmentMappingConfigProvider::OPTION_STANDARD,
                $storeId
            );
            if ($shippingMethod === null) {
                continue;
            }

            /** @var DeliveryInterface $delivery */
            $delivery = $this->deliveryFactory->create();
            $delivery->setDeliveryType($deliveryType);
            $delivery->setDeliveryDate($this->deliveryDateProvider->calculateDeliveryDate($shippingMethod));
            $deliverPrice = $delivery->getDeliveryPrice();
            $deliverPrice->setNet(DecimalCalculator::round((float)$shippingMethod->getPriceExclTax()));
            $deliverPrice->setGross(DecimalCalculator::round((float)$shippingMethod->getPriceInclTax()));
            $deliverPrice->setVat(DecimalCalculator::sub($deliverPrice->getGross(), $deliverPrice->getNet()));
            $delivery->setDeliveryPrice($deliverPrice);

            $freeShippingLimit = $this->getFreeShippingLimit($shippingMethod, $storeId);
            if ($freeShippingLimit) {
                $delivery->setFreeDeliveryMinimumGrossPrice($freeShippingLimit);
            }

            $optionsData = [];
            foreach ($this->shipmentMappingConfigProvider->getNonStandardDeliveryOptions() as $optionCode) {
                $optionShippingMethod = $this->getDeliveryByTypeAndOption(
                    $quoteAvailableShippingMethods,
                    $deliveryType,
                    $optionCode,
                    $storeId
                );

                if ($optionShippingMethod === null) {
                    continue;
                }

                $deliveryOption = $this->getDeliveryOption($optionShippingMethod, $deliverPrice, $optionCode);
                $optionsData[] = $deliveryOption;
            }

            $delivery->setDeliveryOptions($optionsData);
            $deliveryData[] = $delivery;
        }

        return $deliveryData;
    }

    /**
     * @param int $storeId
     * @return array
     */
    private function prepareDigitalDeliveryData(int $storeId): array
    {
        $deliveryData = [];
        /** @var DeliveryInterface $delivery */
        $delivery = $this->deliveryFactory->create();
        $delivery->setDeliveryType(InPostDeliveryType::DIGITAL->value);
        $delivery->setDeliveryDate($this->deliveryDateProvider->calculateDigitalDeliveryDate($storeId));
        $deliverPrice = $delivery->getDeliveryPrice();
        $deliverPrice->setNet(0);
        $deliverPrice->setGross(0);
        $deliverPrice->setVat(0);
        $delivery->setDeliveryPrice($deliverPrice);
        $delivery->setDeliveryOptions([]);
        $deliveryData[] = $delivery;

        return $deliveryData;
    }

    private function getDeliveryOption(
        ShippingMethodInterface $optionShippingMethod,
        PriceInterface $standardDeliveryPrice,
        string $optionCode
    ): DeliveryOptionInterface {
        /** @var DeliveryOptionInterface $deliveryOption */
        $deliveryOption = $this->deliveryOptionFactory->create();
        $deliveryOption->setDeliveryName((string)$optionShippingMethod->getMethodTitle());
        $deliveryOption->setDeliveryCodeValue($optionCode);
        $optionPrice = $deliveryOption->getDeliveryOptionPrice();

        $optionPriceNet = DecimalCalculator::round((float)$optionShippingMethod->getPriceExclTax());
        $optionPriceGross = DecimalCalculator::round((float)$optionShippingMethod->getPriceInclTax());
        $optionPriceVat = DecimalCalculator::sub($optionPriceGross, $optionPriceNet);

        $optionPriceNetDiff = DecimalCalculator::sub($optionPriceNet, $standardDeliveryPrice->getNet());
        $optionPriceGrossDiff = DecimalCalculator::sub($optionPriceGross, $standardDeliveryPrice->getGross());
        $optionPriceVatDiff = DecimalCalculator::sub($optionPriceVat, $standardDeliveryPrice->getVat());

        $optionPrice->setNet(max($optionPriceNetDiff, 0));
        $optionPrice->setGross(max($optionPriceGrossDiff, 0));
        $optionPrice->setVat(max($optionPriceVatDiff, 0));

        $deliveryOption->setDeliveryOptionPrice($optionPrice);

        return $deliveryOption;
    }

    private function getDeliveryByTypeAndOption(
        array $quoteAvailableShippingMethods,
        string $deliveryType,
        string $option,
        int $storeId
    ): ?ShippingMethodInterface {
        try {
            $mappedMethodCode = $this->shipmentMappingConfigProvider->getCarrierMethodCodeForOptions(
                $deliveryType,
                $option,
                $storeId
            );
            foreach ($quoteAvailableShippingMethods as $shippingMethod) {
                $allowedMethodCode = sprintf(
                    '%s_%s',
                    $shippingMethod->getCarrierCode(),
                    $shippingMethod->getMethodCode()
                );
                if ($shippingMethod instanceof ShippingMethodInterface && $allowedMethodCode === $mappedMethodCode) {
                    $mappedShippingMethod = $shippingMethod;
                    break;
                }
            }
        } catch (InPostPayInternalException $e) {
            $mappedShippingMethod = null;
        }

        return $mappedShippingMethod ?? null;
    }

    private function getFreeShippingLimit(ShippingMethodInterface $pickupPointShippingMethod, int $storeId): ?float
    {
        $limit = null;
        $method = (string)$pickupPointShippingMethod->getMethodCode();
        $code = (string)$pickupPointShippingMethod->getCarrierCode();
        if ($this->shipmentMappingConfigProvider->isFreeShippingEnabledForCarrier($code, $method, $storeId)) {
            $limit = $this->shipmentMappingConfigProvider->getFreeShippingSubtotalForCarrier($code, $method, $storeId);
        }

        return $limit;
    }

    private function setBasketNoticeForGuestUnavailableDigitalProducts(string $basketId): void
    {
        $this->createBasketNotice->execute(
            $basketId,
            InPostPayBasketNoticeInterface::ATTENTION,
            __(
                'Cart contains digital products that cannot be ordered as a not logged in user.'
                . ' Please create account in Merchants website in order to complete this purchase.'
            )->render()
        );
    }

    private function getShippingAddress(Quote $quote): AddressInterface
    {
        $shippingAddress = $quote->getShippingAddress();
        // @phpstan-ignore-next-line
        if ((empty($shippingAddress->getCountryId()) || !$shippingAddress->getPostcode())
            // @phpstan-ignore-next-line
            && $quote->getCustomer()->getId()
        ) {
            try {
                $customerShippingAddress =
                    // @phpstan-ignore-next-line
                    $this->addressRepository->getById($quote->getCustomer()->getDefaultShipping());
                $customerShippingAddress->getCountryId();
                if ($customerShippingAddress->getCountryId()) {
                    $shippingAddress->setCountryId($customerShippingAddress->getCountryId());
                }
            } catch (LocalizedException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        if (empty($shippingAddress->getCountryId())) {
            $shippingAddress->setCountryId(self::DEFAULT_COUNTRY_ID);
        }

        return $shippingAddress;
    }
}
