<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AddressDetailsInterface;
use InPost\InPostPay\Api\OrderProcessingStepInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Enum\InPostDeliveryType;
use InPost\InPostPay\Observer\Quote\UpdateInPostBasketEventObserver;
use InPost\InPostPay\Service\Cart\CartService;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ShippingAddressManagement;
use Psr\Log\LoggerInterface;

class ShippingAddressStep extends OrderProcessingStep implements OrderProcessingStepInterface
{
    public function __construct(
        private readonly AddressInterfaceFactory $addressFactory,
        private readonly ShippingAddressManagement $shippingAddressManagement,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    public function process(Quote $quote, OrderInterface $inPostOrder): void
    {
        $quoteId = (int)(is_scalar($quote->getId()) ? $quote->getId() : null);
        /** @var AddressInterface $shippingAddress */
        $shippingAddress = $this->addressFactory->create();
        $shippingAddress->setCustomerAddressId(null);
        $shippingAddress->setEmail($inPostOrder->getAccountInfo()->getMail());
        $shippingAddress->setFirstname($inPostOrder->getAccountInfo()->getName());
        $shippingAddress->setLastname($inPostOrder->getAccountInfo()->getSurname());
        $shippingAddress->setTelephone($this->combinePhoneNumber($inPostOrder->getDelivery()->getPhoneNumber()));
        if ($inPostOrder->getDelivery()->getDeliveryType() === InPostDeliveryType::APM->name) {
            $clientAddress = $inPostOrder->getAccountInfo()->getClientAddress();
            $shippingAddress->setStreet($this->combineAddressToOneLine($clientAddress->getAddressDetails()));
            $shippingAddress->setCity($clientAddress->getCity());
            $shippingAddress->setPostcode($clientAddress->getPostalCode());
            $shippingAddress->setCountryId($clientAddress->getCountryCode());
        } else {
            $deliveryAddress = $inPostOrder->getDelivery()->getDeliveryAddress();
            $shippingAddress->setStreet($this->combineAddressToOneLine($deliveryAddress->getAddressDetails()));
            $shippingAddress->setCity($deliveryAddress->getCity());
            $shippingAddress->setPostcode($deliveryAddress->getPostalCode());
            $shippingAddress->setCountryId($deliveryAddress->getCountryCode());
        }
        $quote->setShippingAddress($shippingAddress);
        $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
        $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
        $this->shippingAddressManagement->assign($quoteId, $shippingAddress);

        $this->createLog(sprintf('Shipping address has been applied to quote ID: %s', $quoteId));
    }

    private function combineAddressToOneLine(AddressDetailsInterface $addressDetails): string
    {
        $addressLine = $addressDetails->getStreet();
        $addressNumber = implode('/', [$addressDetails->getBuilding(), $addressDetails->getFlat()]);

        return sprintf('%s%s%s', $addressLine, PHP_EOL, trim($addressNumber, '/'));
    }

    private function combinePhoneNumber(PhoneNumberInterface $phoneNumber): string
    {
        return sprintf('%s%s', $phoneNumber->getCountryPrefix(), $phoneNumber->getPhone());
    }
}
