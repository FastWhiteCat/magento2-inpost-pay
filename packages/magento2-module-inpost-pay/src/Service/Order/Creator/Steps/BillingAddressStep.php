<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use InPost\InPostPay\Api\OrderProcessingStepInterface;
use InPost\InPostPay\Model\Dto\Order as OrderDto;
use InPost\InPostPay\Model\Dto\Order\AddressDetails;
use InPost\InPostPay\Model\Dto\Order\InvoiceDetails;
use InPost\InPostPay\Model\Dto\Order\PhoneNumber;
use Magento\Quote\Model\Quote;

class BillingAddressStep extends OrderProcessingStep implements OrderProcessingStepInterface
{
    public function process(Quote $quote, OrderDto $orderDto): void
    {
        $accountAddress = $orderDto->getAccountInfo()->getClientAddress();
        $billingAddress = $quote->getShippingAddress();
        $invoiceDetails = $orderDto->getInvoiceDetails();
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
