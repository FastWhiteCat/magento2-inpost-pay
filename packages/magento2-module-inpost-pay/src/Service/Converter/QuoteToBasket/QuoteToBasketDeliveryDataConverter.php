<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter\QuoteToBasket;

use DateTime;
use InPost\InPostPay\Api\ApiConnector\IziApi\Basket\BasketFieldInterface as Basket;
use InPost\InPostPay\Api\Data\Converter\QuoteToBasketDataConverterInterface;
use InPost\InPostPay\Exception\InPostPayInvalidConfigurationException;
use InPost\InPostPay\Model\Config\Source\AcceptedPaymentTypes;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
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
        private readonly IziApiConfigProvider $iziApiConfigProvider,
        private readonly DeliveryDateProvider $deliveryDateProvider,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly ShippingMethodManagementInterface $shippingManager
    ) {
    }

    public function convert(Quote $quote): array
    {
        $deliveries = [];
        $shippingAddress = $quote->getShippingAddress();
        if (empty($shippingAddress->getCountryId())) {
            $shippingAddress->setCountryId(self::DEFAULT_COUNTRY_ID);
        }
        // @phpstan-ignore-next-line
        $shippingMethods = $this->shippingManager->estimateByExtendedAddress((int)$quote->getId(), $shippingAddress);
        $courierShippingMethod = $this->getCourierShippingMethod($shippingMethods);
        $pickupPointShippingMethod = $this->getPickupShippingMethod($shippingMethods);

        if ($pickupPointShippingMethod) {
            $deliveries[] = $this->getPickupData($pickupPointShippingMethod, $quote);
        }

        if ($courierShippingMethod) {
            $deliveries[] = $this->getCourierData($courierShippingMethod, $quote);
        }

        if (empty($deliveries)) {
            throw new LocalizedException(__('No delivery method is allowed for this basket.'));
        }

        return $deliveries;
    }

    private function getCourierShippingMethod(array $quoteAvailableShippingMethods): ?ShippingMethodInterface
    {
        $courierShippingMethod = null;
        try {
            $methodCodeForInPostCourier = $this->shipmentMappingConfigProvider->getCarrierMethodCodeForInPostCourier();
        } catch (InPostPayInvalidConfigurationException $e) {
            $methodCodeForInPostCourier = null;
        }

        foreach ($quoteAvailableShippingMethods as $shippingMethod) {
            $carrierMethodCode = sprintf('%s_%s', $shippingMethod->getCarrierCode(), $shippingMethod->getMethodCode());
            if ($shippingMethod instanceof ShippingMethodInterface
                && $carrierMethodCode === $methodCodeForInPostCourier
            ) {
                $courierShippingMethod = $shippingMethod;
                break;
            }
        }

        return $courierShippingMethod;
    }

    private function getPickupShippingMethod(array $quoteAvailableShippingMethods): ?ShippingMethodInterface
    {
        $pickupShippingMethod = null;
        try {
            $methodCodeForInPostPickup = $this->shipmentMappingConfigProvider->getCarrierMethodCodeForInPostPickup();
        } catch (InPostPayInvalidConfigurationException $e) {
            $methodCodeForInPostPickup = null;
        }

        foreach ($quoteAvailableShippingMethods as $shippingMethod) {
            $carrierMethodCode = sprintf('%s_%s', $shippingMethod->getCarrierCode(), $shippingMethod->getMethodCode());
            if ($shippingMethod instanceof ShippingMethodInterface
                && $carrierMethodCode === $methodCodeForInPostPickup
            ) {
                $pickupShippingMethod = $shippingMethod;
                break;
            }
        }

        return $pickupShippingMethod;
    }

    private function getCourierData(ShippingMethodInterface $courierShippingMethod, Quote $quote): array
    {
        $courierPriceInclTax = DecimalCalculator::round((float)$courierShippingMethod->getPriceInclTax());
        $courierPriceExclTax = DecimalCalculator::round((float)$courierShippingMethod->getPriceExclTax());
        $courierTaxValue = DecimalCalculator::sub($courierPriceInclTax, $courierPriceExclTax);
        $price = [
            Basket::NET => $courierPriceExclTax,
            Basket::GROSS => $courierPriceInclTax,
            Basket::VAT => $courierTaxValue,
        ];

        $deliveryOptions = [];
        $acceptedPaymentMethods = $this->iziApiConfigProvider->getAcceptedPaymentTypes();
        if (in_array(AcceptedPaymentTypes::CASH_ON_DELIVERY, $acceptedPaymentMethods)) {
            $deliveryOptions[] = [
                Basket::DELIVERY_OPTION_NAME => __('Cash on delivery')->render(),
                Basket::DELIVERY_OPTION_CODE => Basket::DELIVERY_OPTION_CASH_ON_DELIVERY,
                Basket::DELIVERY_OPTION_PRICE => $price,
            ];
        }
        $courierData = [
            Basket::DELIVERY_TYPE => Basket::DELIVERY_TYPE_COURIER,
            Basket::DELIVERY_DATE => $this->formatInPostDate(
                $this->deliveryDateProvider->calculateTimestamp(
                    $courierShippingMethod,
                    $quote
                )
            ),
            Basket::DELIVERY_OPTIONS => $deliveryOptions,
            Basket::DELIVERY_PRICE => $price
        ];

        $freeShippingLimit = $this->getFreeShippingLimit($courierShippingMethod);
        if ($freeShippingLimit) {
            $courierData[Basket::FREE_DELIVERY_MINIMUM_GROSS_PRICE] = $freeShippingLimit;
        }

        return $courierData;
    }

    private function getPickupData(ShippingMethodInterface $pickupPointShippingMethod, Quote $quote): array
    {
        $pickupPriceInclTax = DecimalCalculator::round((float)$pickupPointShippingMethod->getPriceInclTax());
        $pickupPriceExclTax = DecimalCalculator::round((float)$pickupPointShippingMethod->getPriceExclTax());
        $pickupTaxValue = DecimalCalculator::sub($pickupPriceInclTax, $pickupPriceExclTax);
        $pickupData = [
            Basket::DELIVERY_TYPE => Basket::DELIVERY_TYPE_PICKUP,
            Basket::DELIVERY_DATE => $this->formatInPostDate(
                $this->deliveryDateProvider->calculateTimestamp(
                    $pickupPointShippingMethod,
                    $quote
                )
            ),
            Basket::DELIVERY_OPTIONS => [],
            Basket::DELIVERY_PRICE => [
                Basket::NET => $pickupPriceExclTax,
                Basket::GROSS => $pickupPriceInclTax,
                Basket::VAT => $pickupTaxValue,
            ]
        ];

        $freeShippingLimit = $this->getFreeShippingLimit($pickupPointShippingMethod);
        if ($freeShippingLimit) {
            $pickupData[Basket::FREE_DELIVERY_MINIMUM_GROSS_PRICE] = $freeShippingLimit;
        }

        return $pickupData;
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

    private function formatInPostDate(int $deliveryTimestamp): string
    {
        $deliveryDateTime = new DateTime();
        $deliveryDateTime->setTimestamp($deliveryTimestamp);

        return $deliveryDateTime->format(QuoteToBasketSummaryDataConverter::INPOST_DATE_FORMAT);
    }
}
