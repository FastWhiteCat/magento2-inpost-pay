<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AddressDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\ClientAddressInterface;
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
            if ($invoiceDetails->getLegalForm() === InPostInvoiceLegalForm::PERSON->name) {
                $billingAddress->setFirstname($invoiceDetails->getName());
                $billingAddress->setLastname($invoiceDetails->getSurname());
            } else {
                $billingAddress->setCompany($invoiceDetails->getCompanyName());
                $billingAddress->setFirstname($inPostOrder->getAccountInfo()->getName());
                $billingAddress->setLastname($inPostOrder->getAccountInfo()->getSurname());
            }
            $billingAddress->setStreet($this->combineInvoiceAddressArray($invoiceDetails));
            $billingAddress->setCity($invoiceDetails->getCity());
            $billingAddress->setPostcode($invoiceDetails->getPostalCode());
            $billingAddress->setCountryId($invoiceDetails->getCountryCode());
            $billingAddress->setTelephone($this->combinePhoneNumber($inPostOrder->getAccountInfo()->getPhoneNumber()));
            $billingAddress->setVatId($this->combineVatId($invoiceDetails));
        } else {
            $billingAddress->setFirstname($inPostOrder->getAccountInfo()->getName());
            $billingAddress->setLastname($inPostOrder->getAccountInfo()->getSurname());
            $billingAddress->setStreet($this->combineBillingAddressArray($accountAddress));
            $billingAddress->setCity($accountAddress->getCity());
            $billingAddress->setPostcode($accountAddress->getPostalCode());
            $billingAddress->setCountryId($accountAddress->getCountryCode());
            $billingAddress->setTelephone($this->combinePhoneNumber($inPostOrder->getAccountInfo()->getPhoneNumber()));
        }

        if ($quote->getCustomerId() && $billingAddress->getCustomerId() === null) {
            $billingAddress->setCustomerId((int)$quote->getCustomerId());
        }

        $quote->setBillingAddress($billingAddress);
        $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
        $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
        $this->billingAddressManagement->assign($quoteId, $billingAddress);

        $this->createLog(sprintf('Billing address has been applied to quote ID: %s', $quoteId));
    }

    private function combineBillingAddressArray(ClientAddressInterface $clientAddress): array
    {
        $addressDetails =  $clientAddress->getAddressDetails();
        $hasStreet = false;
        $hasBuilding = false;
        $addressArray = [];

        if ($addressDetails->getStreet()) {
            $addressArray[] = $addressDetails->getStreet();
            $hasStreet = true;
        }

        if ($addressDetails->getBuilding()) {
            $addressArray[] = $addressDetails->getBuilding();
            $hasBuilding = true;
        }

        if ($addressDetails->getFlat()) {
            $addressArray[] = $addressDetails->getFlat();
        }

        if (!$hasStreet && !$hasBuilding) {
            $addressArray[] = $clientAddress->getAddress();
        }

        return $addressArray;
    }

    private function combineInvoiceAddressArray(InvoiceDetailsInterface $invoiceDetails): array
    {
        $addressArray = [];
        if ($invoiceDetails->getStreet()) {
            $addressArray[] = $invoiceDetails->getStreet();
        }

        if ($invoiceDetails->getBuilding()) {
            $addressArray[] = $invoiceDetails->getBuilding();
        }

        if ($invoiceDetails->getFlat()) {
            $addressArray[] = $invoiceDetails->getFlat();
        }

        return $addressArray;
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
