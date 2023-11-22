<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter\QuoteToBasket;

use DateTime;
use InPost\InPostPay\Api\Data\Converter\QuoteToBasketDataConverterInterface;
use InPost\InPostPay\Exception\InPostPayInvalidConfigurationException;
use InPost\InPostPay\Model\Config\Source\AcceptedPaymentTypes;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote;
use InPost\InPostPay\Api\ApiConnector\IziApi\Basket\BasketFieldInterface as Basket;
use InPost\InPostPay\Provider\DeliveryDateProvider;

class QuoteToBasketDeliveryDataConverter implements QuoteToBasketDataConverterInterface
{
    public function __construct(
        private readonly IziApiConfigProvider $iziApiConfigProvider,
        private readonly DeliveryDateProvider $deliveryDateProvider,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly ShippingMethodManagementInterface $shippingMethodManager
    ) {
    }

    public function convert(Quote $quote): array
    {
        $deliveries = [];
        if ($quote->getShippingAddress()) {
            $shippingMethods = $this->shippingMethodManager->getList((int)$quote->getId());
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
        } else {

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
        $courierPriceInclTax = round((float)$courierShippingMethod->getPriceInclTax(), 2);
        $courierPriceExclTax = round((float)$courierShippingMethod->getPriceExclTax(), 2);
        $price = [
            Basket::NET => $courierPriceExclTax,
            Basket::GROSS => $courierPriceInclTax,
            Basket::VAT => $courierPriceInclTax - $courierPriceExclTax,
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
        $pickupPriceInclTax = round((float)$pickupPointShippingMethod->getPriceInclTax(), 2);
        $pickupPriceExclTax = round((float)$pickupPointShippingMethod->getPriceExclTax(), 2);
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
                Basket::VAT => $pickupPriceInclTax - $pickupPriceExclTax,
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
