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
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\BillingAddressPersister;
use Psr\Log\LoggerInterface;

class BillingAddressStep extends OrderProcessingStep implements OrderProcessingStepInterface
{
    public function __construct(
        private readonly BillingAddressManagementInterface $billingAddressManagement,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    public function process(Quote $quote, OrderDto $orderDto): void
    {
        //TODO:: create address from scratch
        $accountAddress = $orderDto->getAccountInfo()->getClientAddress();
        $billingAddress = $quote->getBillingAddress();
        $invoiceDetails = $orderDto->getInvoiceDetails();
        if ($invoiceDetails) {
            $billingAddress->setEmail($orderDto->getAccountInfo()->getMail());
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
            $billingAddress->setRegionId(801);
        } else {
            $billingAddress->setEmail($orderDto->getAccountInfo()->getMail());
            $billingAddress->setFirstname($orderDto->getAccountInfo()->getName());
            $billingAddress->setLastname($orderDto->getAccountInfo()->getSurname());
            $billingAddress->setStreet(
                $this->combineAddressToOneLine($orderDto->getAccountInfo()->getClientAddress()->getAddressDetails())
            );
            $billingAddress->setCity($accountAddress->getCity());
            $billingAddress->setPostcode($accountAddress->getPostalCode());
            $billingAddress->setCountryId($accountAddress->getCountryCode());
            $billingAddress->setTelephone($this->combinePhoneNumber($orderDto->getAccountInfo()->getPhoneNumber()));
            $billingAddress->setRegionId(801);
        }
        $quote->setBillingAddress($billingAddress);
        $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
        $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
        $this->billingAddressManagement->assign((int)$quote->getId(), $billingAddress);

        $this->createLog(sprintf('Billing address has been applied to quote ID: %s', (int)$quote->getId()));
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

    private function combineInvoiceAddressToOneLine(InvoiceDetails $invoiceDetails): string
    {
        $addressLine = $invoiceDetails->getStreet();
        $addressNumber = implode('/', [$invoiceDetails->getBuilding(), $invoiceDetails->getFlat()]);
        if ($addressNumber) {
            $addressLine = sprintf('%s %s', $addressLine, $addressNumber);
        }

        return $addressLine;
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
