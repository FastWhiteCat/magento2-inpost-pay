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
use InPost\InPostPay\Registry\Quote\DigitalQuoteAllowRegistry;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use InPost\InPostPay\Service\Cart\ShippingMethod\ShippingMethodEstimator;
use InPost\InPostPay\Service\CreateBasketNotice;
use InPost\InPostPay\Validator\QuoteRestrictionsValidator;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteToBasketDeliveryDataTransfer implements QuoteToBasketDataTransferInterface
{

    /**
     * @param DeliveryInterfaceFactory $deliveryFactory
     * @param DeliveryOptionInterfaceFactory $deliveryOptionFactory
     * @param DeliveryDateProvider $deliveryDateProvider
     * @param ShipmentMappingConfigProvider $shipmentMappingConfigProvider
     * @param CreateBasketNotice $createBasketNotice
     * @param QuoteRestrictionsValidator $quoteRestrictionsValidator
     * @param ShippingMethodEstimator $shippingMethodEstimator
     * @param DigitalQuoteAllowRegistry $digitalQuoteAllowRegistry
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly DeliveryInterfaceFactory $deliveryFactory,
        private readonly DeliveryOptionInterfaceFactory $deliveryOptionFactory,
        private readonly DeliveryDateProvider $deliveryDateProvider,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly CreateBasketNotice $createBasketNotice,
        private readonly QuoteRestrictionsValidator $quoteRestrictionsValidator,
        private readonly ShippingMethodEstimator $shippingMethodEstimator,
        private readonly DigitalQuoteAllowRegistry $digitalQuoteAllowRegistry,
        private readonly LoggerInterface $logger
    ) {
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $storeId = $quote->getStoreId();

        if (!$this->digitalQuoteAllowRegistry->isCurrentlyProcessedDigitalQuoteAllowed()) {
            $basket->setDelivery([]);

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
            $shippingMethods = $this->shippingMethodEstimator->estimate($quote);
            $deliveries = $this->prepareMappedShippingMethodsData($shippingMethods, $storeId);

            if ($quote->hasVirtualItems()) {
                $deliveries = array_merge($deliveries, $this->prepareDigitalDeliveryData($storeId));
            }
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
}
