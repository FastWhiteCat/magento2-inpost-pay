<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Order;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;
use InPost\InPostPay\Exception\InPostPayInvalidConfigurationException;
use InPost\InPostPay\Model\Dto\Order as DtoOrder;
use InPost\InPostPay\Model\Dto\Order\Delivery;
use InPost\InPostPay\Model\Dto\Order\DeliveryAddress;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use InPost\InPostPay\Api\ApiConnector\IziApi\Basket\BasketFieldInterface;

class DeliveryValidator implements OrderValidatorInterface
{
    public function __construct(
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider
    ) {
    }

    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, DtoOrder $orderDto): void
    {
        $this->validateDeliveryAddress($orderDto->getDelivery()->getDeliveryAddress());
        $this->validateDeliveryMethod($orderDto->getDelivery());
    }

    /**
     * @param DeliveryAddress $deliveryAddress
     * @return void
     * @throws LocalizedException
     */
    private function validateDeliveryAddress(DeliveryAddress $deliveryAddress): void
    {
        $addressDetails = $deliveryAddress->getAddressDetails();
        if (empty($deliveryAddress->getCity())
            || empty($deliveryAddress->getCountryCode())
            || empty($deliveryAddress->getPostalCode())
            || empty($addressDetails->getStreet())
            || (empty($addressDetails->getBuilding()) && empty($addressDetails->getFlat()))
        ) {
            throw new LocalizedException(__('Incomplete delivery address data.'));
        }
    }

    /**
     * @param Delivery $delivery
     * @return void
     * @throws InPostPayInvalidConfigurationException
     * @throws LocalizedException
     */
    private function validateDeliveryMethod(Delivery $delivery): void
    {
        $deliveryType = $delivery->getDeliveryType();
        $deliveryMethod = null;
        if ($deliveryType === BasketFieldInterface::DELIVERY_TYPE_COURIER) {
            $deliveryMethod = $this->shipmentMappingConfigProvider->getCarrierMethodCodeForInPostCourier();
        } elseif ($deliveryType === BasketFieldInterface::DELIVERY_TYPE_PICKUP) {
            $deliveryMethod = $this->shipmentMappingConfigProvider->getCarrierMethodCodeForInPostPickup();
            if (empty($delivery->getDeliveryPoint())) {
                throw new LocalizedException(__('Delivery method %1 requires chosen point.', $deliveryType));
            }
        }

        if (empty($deliveryMethod)) {
            throw new LocalizedException(__('Selected delivery method %1 is not available.', $deliveryType));
        }
    }
}
