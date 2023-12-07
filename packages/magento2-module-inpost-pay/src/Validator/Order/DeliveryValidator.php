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
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Quote;
use InPost\InPostPay\Api\ApiConnector\IziApi\Basket\BasketFieldInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;

class DeliveryValidator implements OrderValidatorInterface
{
    private const DEFAULT_COUNTRY_ID = 'PL';

    public function __construct(
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly ShippingMethodManagementInterface $shippingManager
    ) {
    }

    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, DtoOrder $orderDto): void
    {
        $this->validateDeliveryAddress($orderDto->getDelivery()->getDeliveryAddress());
        $this->validateDeliveryMethod($orderDto->getDelivery(), $quote);
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
     * @param Quote $quote
     * @return void
     * @throws LocalizedException
     */
    private function validateDeliveryMethod(Delivery $delivery, Quote $quote): void
    {
        $deliveryType = $delivery->getDeliveryType();
        if (empty($delivery->getDeliveryCodes())) {
            $deliveryOption = ShipmentMappingConfigProvider::OPTION_STANDARD;
        } else {
            $deliveryOption = implode('', $delivery->getDeliveryCodes());
        }

        try {
            $deliveryMethod = $this->shipmentMappingConfigProvider->getCarrierMethodCodeForOptions(
                $deliveryType,
                $deliveryOption
            );
        } catch (InPostPayInvalidConfigurationException $e) {
            throw new LocalizedException(__('Selected delivery method %1 is not available.', $deliveryType));
        }

        if (!$this->isDeliveryMethodAvailableForQuote($deliveryMethod, $quote)) {
            throw new LocalizedException(
                __(
                    'Selected delivery method %1[%2] is not available for this basket.',
                    $deliveryType,
                    $deliveryOption
                )
            );
        }

        if ($deliveryType === BasketFieldInterface::DELIVERY_TYPE_PICKUP
            && empty($delivery->getDeliveryPoint())
        ) {
            throw new LocalizedException(__('Delivery method %1 requires chosen point.', $deliveryType));
        }
    }

    private function isDeliveryMethodAvailableForQuote(string $deliveryMethod, Quote $quote): bool
    {
        $shippingAddress = $quote->getShippingAddress();
        if (empty($shippingAddress->getCountryId())) {
            $shippingAddress->setCountryId(self::DEFAULT_COUNTRY_ID);
        }
        // @phpstan-ignore-next-line
        $shippingMethods = $this->shippingManager->estimateByExtendedAddress((int)$quote->getId(), $shippingAddress);
        foreach ($shippingMethods as $shippingMethod) {
            $allowedMethodCode = sprintf('%s_%s', $shippingMethod->getCarrierCode(), $shippingMethod->getMethodCode());
            if ($shippingMethod instanceof ShippingMethodInterface && $allowedMethodCode === $deliveryMethod) {
                return true;
            }
        }

        return false;
    }
}
