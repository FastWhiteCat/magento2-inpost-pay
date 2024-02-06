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
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Exception\InPostPayRestrictedProductException;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use InPost\InPostPay\Provider\Delivery\DeliveryDateProvider;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use InPost\InPostPay\Service\CreateBasketNotice;
use Magento\Customer\Api\AddressRepositoryInterface;
use InPost\InPostPay\Validator\QuoteRestrictionsValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteToBasketDeliveryDataTransfer implements QuoteToBasketDataTransferInterface
{
    private const DEFAULT_COUNTRY_ID = 'PL';

    public function __construct(
        private readonly DeliveryInterfaceFactory $deliveryFactory,
        private readonly DeliveryOptionInterfaceFactory $deliveryOptionFactory,
        private readonly DeliveryDateProvider $deliveryDateProvider,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly ShippingMethodManagementInterface $shippingManager,
        private readonly AddressRepositoryInterface $addressRepository,
        private readonly CreateBasketNotice $createBasketNotice,
        private readonly QuoteRestrictionsValidator $quoteRestrictionsValidator,
        private readonly LoggerInterface $logger
    ) {
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $shippingAddress = $this->getShippingAddress($quote);

        if ($quote->isVirtual()) {
            $this->logger->error('Quote is virtual. Setting empty delivery.');
            $basket->setDelivery([]);
            $this->setBasketNoticeVirtualProducts((string)$basket->getBasketId());
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

        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProduct()->getIsVirtual()) {
                $this->setBasketNoticeVirtualProducts((string)$basket->getBasketId());
                break;
            }
        }

        // @phpstan-ignore-next-line
        $shippingMethods = $this->shippingManager->estimateByExtendedAddress((int)$quote->getId(), $shippingAddress);
        $deliveries = $this->prepareMappedShippingMethodsData($shippingMethods);

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
     * @param DeliveryInterface[] $quoteAvailableShippingMethods
     * @return array
     */
    private function prepareMappedShippingMethodsData(array $quoteAvailableShippingMethods): array
    {
        $deliveryData = [];
        foreach ($this->shipmentMappingConfigProvider->getAllDeliveryTypes() as $deliveryType) {
            $shippingMethod = $this->getDeliveryByTypeAndOption(
                $quoteAvailableShippingMethods,
                $deliveryType,
                ShipmentMappingConfigProvider::OPTION_STANDARD
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

            $freeShippingLimit = $this->getFreeShippingLimit($shippingMethod);
            if ($freeShippingLimit) {
                $delivery->setFreeDeliveryMinimumGrossPrice($freeShippingLimit);
            }

            $optionsData = [];
            foreach ($this->shipmentMappingConfigProvider->getNonStandardDeliveryOptions() as $optionCode) {
                $optionShippingMethod = $this->getDeliveryByTypeAndOption(
                    $quoteAvailableShippingMethods,
                    $deliveryType,
                    $optionCode
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
        string $option
    ): ?ShippingMethodInterface {
        try {
            $mappedMethodCode = $this->shipmentMappingConfigProvider->getCarrierMethodCodeForOptions(
                $deliveryType,
                $option
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

    private function getFreeShippingLimit(ShippingMethodInterface $pickupPointShippingMethod): ?float
    {
        $limit = null;
        $method = (string)$pickupPointShippingMethod->getMethodCode();
        $code = (string)$pickupPointShippingMethod->getCarrierCode();
        if ($this->shipmentMappingConfigProvider->isFreeShippingEnabledForCarrier($code, $method)) {
            $limit = $this->shipmentMappingConfigProvider->getFreeShippingSubtotalForCarrier($code, $method);
        }

        return $limit;
    }

    private function setBasketNoticeVirtualProducts(string $basketId): void
    {
        $this->createBasketNotice->execute(
            $basketId,
            InPostPayBasketNoticeInterface::ATTENTION,
            __('Order contains products that cannot be shipped.')->render()
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
                // @phpstan-ignore-next-line
                $customerShippingAddress = $this->addressRepository->getById($quote->getCustomer()->getDefaultShipping());
                $customerShippingAddress->getCountryId();
                if ($customerShippingAddress->getCountryId()) {
                    $shippingAddress->setCountryId($customerShippingAddress->getCountryId());
                }
            } catch (LocalizedException $e) {
            }
        }

        if (empty($shippingAddress->getCountryId())) {
            $shippingAddress->setCountryId(self::DEFAULT_COUNTRY_ID);
        }

        return $shippingAddress;
    }
}
