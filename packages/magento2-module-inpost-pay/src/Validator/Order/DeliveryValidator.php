<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Order;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryAddressInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;
use InPost\InPostPay\Enum\InPostDeliveryType;
use InPost\InPostPay\Exception\InPostPayInvalidConfigurationException;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote;

class DeliveryValidator implements OrderValidatorInterface
{
    private const DEFAULT_COUNTRY_ID = 'PL';

    public function __construct(
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly ShippingMethodManagementInterface $shippingManager
    ) {
    }

    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, OrderInterface $inPostOrder): void
    {
        $this->validateDeliveryAddress($inPostOrder->getDelivery()->getDeliveryAddress());
        $this->validateDeliveryMethod($inPostOrder->getDelivery(), $quote);
    }

    /**
     * @param DeliveryAddressInterface $deliveryAddress
     * @return void
     * @throws LocalizedException
     */
    private function validateDeliveryAddress(DeliveryAddressInterface $deliveryAddress): void
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
     * @param DeliveryInterface $delivery
     * @param Quote $quote
     * @return void
     * @throws LocalizedException
     */
    private function validateDeliveryMethod(DeliveryInterface $delivery, Quote $quote): void
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

        if ($deliveryType === InPostDeliveryType::APM->name && empty($delivery->getDeliveryPoint())) {
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
