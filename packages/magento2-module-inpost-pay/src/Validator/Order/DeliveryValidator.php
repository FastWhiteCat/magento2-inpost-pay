<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Order;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryAddressInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;
use InPost\InPostPay\Enum\InPostDeliveryType;
use InPost\InPostPay\Exception\InPostPayInternalException;
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
        if ($inPostOrder->getDelivery()->getDeliveryType() !== InPostDeliveryType::APM->name) {
            $this->validateDeliveryAddress($inPostOrder->getDelivery()->getDeliveryAddress());
        }
        $this->validateDeliveryMethod($inPostOrder->getDelivery(), $quote);
    }

    /**
     * @param DeliveryAddressInterface $deliveryAddress
     * @return void
     * @throws LocalizedException
     */
    private function validateDeliveryAddress(DeliveryAddressInterface $deliveryAddress): void
    {
        if (empty($deliveryAddress->getCity())) {
            throw new LocalizedException(__('Incomplete delivery address city data.'));
        }

        if (empty($deliveryAddress->getCountryCode())) {
            throw new LocalizedException(__('Incomplete delivery address country data.'));
        }

        if (empty($deliveryAddress->getPostalCode())) {
            throw new LocalizedException(__('Incomplete delivery address postal code data.'));
        }

        if (empty($deliveryAddress->getAddress())) {
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
        } catch (InPostPayInternalException $e) {
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
