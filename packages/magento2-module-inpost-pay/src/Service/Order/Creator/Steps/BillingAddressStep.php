<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use InPost\InPostPay\Api\OrderProcessingStepInterface;
use InPost\InPostPay\Model\Dto\Order as OrderDto;
use InPost\InPostPay\Model\Dto\Order\AddressDetails;
use InPost\InPostPay\Model\Dto\Order\InvoiceDetails;
use InPost\InPostPay\Model\Dto\Order\PhoneNumber;
use InPost\InPostPay\Observer\Quote\UpdateInPostBasketEventObserver;
use InPost\InPostPay\Service\Cart\CartService;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class BillingAddressStep extends OrderProcessingStep implements OrderProcessingStepInterface
{
    public function __construct(
        private readonly AddressInterfaceFactory $addressFactory,
        private readonly BillingAddressManagementInterface $billingAddressManagement,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    public function process(Quote $quote, OrderDto $orderDto): void
    {
        $quoteId = (int)(is_scalar($quote->getId()) ? $quote->getId() : null);
        $accountAddress = $orderDto->getAccountInfo()->getClientAddress();
        $invoiceDetails = $orderDto->getInvoiceDetails();
        /** @var AddressInterface $billingAddress */
        $billingAddress = $this->addressFactory->create();
        $billingAddress->setEmail($orderDto->getAccountInfo()->getMail());
        if ($invoiceDetails) {
            $billingAddress->setFirstname($invoiceDetails->getName());
            $billingAddress->setLastname($invoiceDetails->getSurname());
            $billingAddress->setCompany($invoiceDetails->getCompanyName());
            $billingAddress->setStreet(
                $this->combineInvoiceAddressToOneLine($invoiceDetails)
            );
            $billingAddress->setCity($invoiceDetails->getCity());
            $billingAddress->setPostcode($invoiceDetails->getPostalCode());
            $billingAddress->setCountryId($invoiceDetails->getCountryCode());
            $billingAddress->setTelephone($this->combinePhoneNumber($orderDto->getAccountInfo()->getPhoneNumber()));
            $billingAddress->setVatId($this->combineVatId($invoiceDetails));
        } else {
            $billingAddress->setFirstname($orderDto->getAccountInfo()->getName());
            $billingAddress->setLastname($orderDto->getAccountInfo()->getSurname());
            $billingAddress->setStreet(
                $this->combineAddressToOneLine($orderDto->getAccountInfo()->getClientAddress()->getAddressDetails())
            );
            $billingAddress->setCity($accountAddress->getCity());
            $billingAddress->setPostcode($accountAddress->getPostalCode());
            $billingAddress->setCountryId($accountAddress->getCountryCode());
            $billingAddress->setTelephone($this->combinePhoneNumber($orderDto->getAccountInfo()->getPhoneNumber()));
        }
        $quote->setBillingAddress($billingAddress);
        $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
        $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
        $this->billingAddressManagement->assign($quoteId, $billingAddress);

        $this->createLog(sprintf('Billing address has been applied to quote ID: %s', $quoteId));
    }

    private function combineAddressToOneLine(AddressDetails $addressDetails): string
    {
        $addressLine = $addressDetails->getStreet();
        $addressNumber = implode('/', [$addressDetails->getBuilding(), $addressDetails->getFlat()]);

        return sprintf('%s %s', $addressLine, trim($addressNumber, '/'));
    }

    private function combineInvoiceAddressToOneLine(InvoiceDetails $invoiceDetails): string
    {
        $addressLine = $invoiceDetails->getStreet();
        $addressNumber = implode('/', [$invoiceDetails->getBuilding(), $invoiceDetails->getFlat()]);

        return sprintf('%s %s', $addressLine, trim($addressNumber, '/'));
    }

    private function combinePhoneNumber(PhoneNumber $phoneNumber): string
    {
        return sprintf('%s%s', $phoneNumber->getCountryPrefix(), $phoneNumber->getPhone());
    }

    private function combineVatId(InvoiceDetails $invoiceDetails): string
    {
        return sprintf('%s%s', $invoiceDetails->getTaxIdPrefix(), $invoiceDetails->getTaxId());
    }
}
