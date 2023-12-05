<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use InPost\InPostPay\Api\OrderProcessingStepInterface;
use InPost\InPostPay\Model\Dto\Order as OrderDto;
use InPost\InPostPay\Model\Dto\Order\AddressDetails;
use InPost\InPostPay\Model\Dto\Order\PhoneNumber;
use Magento\Quote\Model\Quote;

class ShippingAddressStep extends OrderProcessingStep implements OrderProcessingStepInterface
{
    public function process(Quote $quote, OrderDto $orderDto): void
    {
        $deliveryAddress = $orderDto->getDelivery()->getDeliveryAddress();
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setFirstname($orderDto->getAccountInfo()->getName());
        $shippingAddress->setLastname($orderDto->getAccountInfo()->getSurname());
        $shippingAddress->setStreet(
            $this->combineAddressToOneLine($deliveryAddress->getAddressDetails())
        );
        $shippingAddress->setCity($deliveryAddress->getCity());
        $shippingAddress->setPostcode($deliveryAddress->getPostalCode());
        $shippingAddress->setCountryId($deliveryAddress->getCountryCode());
        $shippingAddress->setTelephone($this->combinePhoneNumber($orderDto->getDelivery()->getPhoneNumber()));

        $this->createLog(sprintf('Shipping address has been applied to quote ID: %s', (int)$quote->getId()));
    }

    private function combineAddressToOneLine(AddressDetails $addressDetails): string
    {
        $addressLine = $addressDetails->getStreet();
        $addressNumber = implode('/', [$addressDetails->getBuilding(), $addressDetails->getFlat()]);
        if ($addressNumber) {
            $addressLine = sprintf('%s %s', $addressLine, $addressNumber);
        }

        return $addressLine;
    }

    private function combinePhoneNumber(PhoneNumber $phoneNumber): string
    {
        return sprintf('%s%s', $phoneNumber->getCountryPrefix(), $phoneNumber->getPhone());
    }
}
