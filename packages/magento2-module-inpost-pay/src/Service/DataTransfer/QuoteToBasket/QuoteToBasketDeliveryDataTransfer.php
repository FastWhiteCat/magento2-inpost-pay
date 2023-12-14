<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\DeliveryInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use InPost\InPostPay\Provider\Delivery\DeliveryDateProvider;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote;

class QuoteToBasketDeliveryDataTransfer implements QuoteToBasketDataTransferInterface
{
    private const DEFAULT_COUNTRY_ID = 'PL';

    public function __construct(
        private readonly DeliveryInterfaceFactory $deliveryFactory,
        private readonly DeliveryOptionInterfaceFactory $deliveryOptionFactory,
        private readonly DeliveryDateProvider $deliveryDateProvider,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly ShippingMethodManagementInterface $shippingManager
    ) {
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $shippingAddress = $quote->getShippingAddress();
        if (empty($shippingAddress->getCountryId())) {
            $shippingAddress->setCountryId(self::DEFAULT_COUNTRY_ID);
        }
        // @phpstan-ignore-next-line
        $shippingMethods = $this->shippingManager->estimateByExtendedAddress((int)$quote->getId(), $shippingAddress);
        $deliveries = $this->prepareMappedShippingMethodsData($shippingMethods);

        if (empty($deliveries)) {
            throw new LocalizedException(__('No delivery method is allowed for this basket.'));
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

                /** @var DeliveryOptionInterface $deliveryOption */
                $deliveryOption = $this->deliveryOptionFactory->create();
                $deliveryOption->setDeliveryName((string)$optionShippingMethod->getMethodTitle());
                $deliveryOption->setDeliveryCodeValue($optionCode);
                $optionPrice = $deliveryOption->getDeliveryOptionPrice();
                $optionPrice->setNet(DecimalCalculator::round((float)$optionShippingMethod->getPriceExclTax()));
                $optionPrice->setGross(DecimalCalculator::round((float)$optionShippingMethod->getPriceInclTax()));
                $optionPrice->setVat(DecimalCalculator::sub($optionPrice->getGross(), $optionPrice->getNet()));
                $deliveryOption->setDeliveryOptionPrice($optionPrice);
                $optionsData[] = $deliveryOption;
            }

            $delivery->setDeliveryOptions($optionsData);
            $deliveryData[] = $delivery;
        }

        return $deliveryData;
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
}
