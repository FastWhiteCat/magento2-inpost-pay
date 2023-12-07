<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use InPost\InPostPay\Api\OrderProcessingStepInterface;
use InPost\InPostPay\Model\Dto\Order as OrderDto;
use InPost\InPostPay\Model\Dto\Order\AddressDetails;
use InPost\InPostPay\Model\Dto\Order\PhoneNumber;
use InPost\InPostPay\Observer\Quote\UpdateInPostBasketEventObserver;
use InPost\InPostPay\Service\Cart\CartService;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ShippingAddressManagement;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\AddressInterface;
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

    public function process(Quote $quote, OrderDto $orderDto): void
    {
        $quoteId = (int)(is_scalar($quote->getId()) ? $quote->getId() : null);
        $deliveryAddress = $orderDto->getDelivery()->getDeliveryAddress();
        /** @var AddressInterface $shippingAddress */
        $shippingAddress = $this->addressFactory->create();
        $shippingAddress->setCustomerAddressId(null);
        $shippingAddress->setEmail($orderDto->getAccountInfo()->getMail());
        $shippingAddress->setFirstname($orderDto->getAccountInfo()->getName());
        $shippingAddress->setLastname($orderDto->getAccountInfo()->getSurname());
        $shippingAddress->setStreet(
            $this->combineAddressToOneLine($deliveryAddress->getAddressDetails())
        );
        $shippingAddress->setCity($deliveryAddress->getCity());
        $shippingAddress->setPostcode($deliveryAddress->getPostalCode());
        $shippingAddress->setCountryId($deliveryAddress->getCountryCode());
        $shippingAddress->setTelephone($this->combinePhoneNumber($orderDto->getDelivery()->getPhoneNumber()));
        $quote->setShippingAddress($shippingAddress);
        $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
        $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
        $this->shippingAddressManagement->assign($quoteId, $shippingAddress);

        $this->createLog(sprintf('Shipping address has been applied to quote ID: %s', $quoteId));
    }

    private function combineAddressToOneLine(AddressDetails $addressDetails): string
    {
        $addressLine = $addressDetails->getStreet();
        $addressNumber = implode('/', [$addressDetails->getBuilding(), $addressDetails->getFlat()]);

        return sprintf('%s %s', $addressLine, trim($addressNumber, '/'));
    }

    private function combinePhoneNumber(PhoneNumber $phoneNumber): string
    {
        return sprintf('%s%s', $phoneNumber->getCountryPrefix(), $phoneNumber->getPhone());
    }
}
