<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AddressDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface;
use InPost\InPostPay\Api\OrderProcessingStepInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Enum\InPostInvoiceLegalForm;
use InPost\InPostPay\Observer\Quote\UpdateInPostBasketEventObserver;
use InPost\InPostPay\Service\Cart\CartService;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
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

    public function process(Quote $quote, OrderInterface $inPostOrder): void
    {
        $quoteId = (int)(is_scalar($quote->getId()) ? $quote->getId() : null);
        $accountAddress = $inPostOrder->getAccountInfo()->getClientAddress();
        $invoiceDetails = $inPostOrder->getInvoiceDetails();
        /** @var AddressInterface $billingAddress */
        $billingAddress = $this->addressFactory->create();
        $billingAddress->setEmail($inPostOrder->getAccountInfo()->getMail());
        if ($invoiceDetails) {
            if ($inPostOrder->getInvoiceDetails()->getLegalForm() === InPostInvoiceLegalForm::COMPANY->name) {
                $billingAddress->setFirstname($invoiceDetails->getName());
                $billingAddress->setLastname($invoiceDetails->getSurname());
                $billingAddress->setCompany($invoiceDetails->getCompanyName());
            } else {
                $billingAddress->setFirstname($inPostOrder->getAccountInfo()->getName());
                $billingAddress->setLastname($inPostOrder->getAccountInfo()->getSurname());
            }
            $billingAddress->setStreet(
                $this->combineInvoiceAddressToOneLine($invoiceDetails)
            );
            $billingAddress->setCity($invoiceDetails->getCity());
            $billingAddress->setPostcode($invoiceDetails->getPostalCode());
            $billingAddress->setCountryId($invoiceDetails->getCountryCode());
            $billingAddress->setTelephone($this->combinePhoneNumber($inPostOrder->getAccountInfo()->getPhoneNumber()));
            $billingAddress->setVatId($this->combineVatId($invoiceDetails));
        } else {
            $billingAddress->setFirstname($inPostOrder->getAccountInfo()->getName());
            $billingAddress->setLastname($inPostOrder->getAccountInfo()->getSurname());
            $billingAddress->setStreet(
                $this->combineAddressToOneLine($inPostOrder->getAccountInfo()->getClientAddress()->getAddressDetails())
            );
            $billingAddress->setCity($accountAddress->getCity());
            $billingAddress->setPostcode($accountAddress->getPostalCode());
            $billingAddress->setCountryId($accountAddress->getCountryCode());
            $billingAddress->setTelephone($this->combinePhoneNumber($inPostOrder->getAccountInfo()->getPhoneNumber()));
        }
        $quote->setBillingAddress($billingAddress);
        $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
        $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
        $this->billingAddressManagement->assign($quoteId, $billingAddress);

        $this->createLog(sprintf('Billing address has been applied to quote ID: %s', $quoteId));
    }

    private function combineAddressToOneLine(AddressDetailsInterface $addressDetails): string
    {
        $addressLine = $addressDetails->getStreet();
        $addressNumber = implode('/', [$addressDetails->getBuilding(), $addressDetails->getFlat()]);

        return sprintf('%s %s', $addressLine, trim($addressNumber, '/'));
    }

    private function combineInvoiceAddressToOneLine(InvoiceDetailsInterface $invoiceDetails): string
    {
        $addressLine = $invoiceDetails->getStreet();
        $addressNumber = implode('/', [$invoiceDetails->getBuilding(), $invoiceDetails->getFlat()]);

        return sprintf('%s%s%s', $addressLine, PHP_EOL, trim($addressNumber, '/'));
    }

    private function combinePhoneNumber(PhoneNumberInterface $phoneNumber): string
    {
        return sprintf('%s%s', $phoneNumber->getCountryPrefix(), $phoneNumber->getPhone());
    }

    private function combineVatId(InvoiceDetailsInterface $invoiceDetails): string
    {
        return sprintf('%s%s', $invoiceDetails->getTaxIdPrefix(), $invoiceDetails->getTaxId());
    }
}
