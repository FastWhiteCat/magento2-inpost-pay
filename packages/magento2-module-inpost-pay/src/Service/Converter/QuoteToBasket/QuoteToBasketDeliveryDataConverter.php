<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter\QuoteToBasket;

use InPost\InPostPay\Api\ApiConnector\IziApi\Basket\BasketFieldInterface as Basket;
use InPost\InPostPay\Api\Data\Converter\QuoteToBasketDataConverterInterface;
use InPost\InPostPay\Exception\InPostPayInvalidConfigurationException;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use InPost\InPostPay\Provider\Delivery\DeliveryDateProvider;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote;

class QuoteToBasketDeliveryDataConverter implements QuoteToBasketDataConverterInterface
{
    private const DEFAULT_COUNTRY_ID = 'PL';

    public function __construct(
        private readonly DeliveryDateProvider $deliveryDateProvider,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly ShippingMethodManagementInterface $shippingManager
    ) {
    }

    public function convert(Quote $quote): array
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

        return $deliveries;
    }

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

            $deliveryTypeData = [
                Basket::DELIVERY_TYPE => $deliveryType,
                Basket::DELIVERY_DATE => $this->deliveryDateProvider->calculateDeliveryDate($shippingMethod),
                Basket::DELIVERY_PRICE => $this->getDeliveryMethodPricing($shippingMethod)
            ];

            $freeShippingLimit = $this->getFreeShippingLimit($shippingMethod);
            if ($freeShippingLimit) {
                $deliveryTypeData[Basket::FREE_DELIVERY_MINIMUM_GROSS_PRICE] = $freeShippingLimit;
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

                $optionsData[] = [
                    Basket::DELIVERY_OPTION_NAME => $optionShippingMethod->getMethodTitle(),
                    Basket::DELIVERY_OPTION_CODE => $optionCode,
                    Basket::DELIVERY_OPTION_PRICE => $this->getDeliveryMethodPricing($optionShippingMethod),
                ];
            }

            $deliveryTypeData[Basket::DELIVERY_OPTIONS] = $optionsData;
            $deliveryData[] = $deliveryTypeData;
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
        } catch (InPostPayInvalidConfigurationException $e) {
            $mappedShippingMethod = null;
        }

        return $mappedShippingMethod ?? null;
    }

    private function getDeliveryMethodPricing(ShippingMethodInterface $pickupPointShippingMethod): array
    {
        $pickupPriceInclTax = DecimalCalculator::round((float)$pickupPointShippingMethod->getPriceInclTax());
        $pickupPriceExclTax = DecimalCalculator::round((float)$pickupPointShippingMethod->getPriceExclTax());
        $pickupTaxValue = DecimalCalculator::sub($pickupPriceInclTax, $pickupPriceExclTax);

        return [
            Basket::NET => $pickupPriceExclTax,
            Basket::GROSS => $pickupPriceInclTax,
            Basket::VAT => $pickupTaxValue
        ];
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
