<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder;

use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\DataTransfer\OrderToInPostOrderDataTransferInterface;
use InPost\InPostPay\Enum\InPostInvoiceLegalForm;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;

class OrderToInPostOrderInvoiceDetailsDataTransfer implements OrderToInPostOrderDataTransferInterface
{
    public function __construct(
        private readonly InvoiceDetailsInterfaceFactory $invoiceDetailsFactory
    ) {
    }

    public function transfer(Order $order, OrderInterface $inPostOrder): void
    {
        $invoiceDetails = $inPostOrder->getInvoiceDetails();
        if ($invoiceDetails === null) {
            /** @var InvoiceDetailsInterface $invoiceDetails */
            $invoiceDetails = $this->invoiceDetailsFactory->create();
        }
        $billingAddress = $order->getBillingAddress();
        if ($billingAddress) {
            $invoiceDetails->setMail((string)$billingAddress->getEmail());
            $invoiceDetails->setName((string)$billingAddress->getFirstname());
            $invoiceDetails->setSurname((string)$billingAddress->getLastname());
            $invoiceDetails->setCity((string)$billingAddress->getCity());
            $invoiceDetails->setCountryCode((string)$billingAddress->getCountryId());
            $invoiceDetails->setPostalCode((string)$billingAddress->getPostcode());
            $this->transferInvoiceAddressDetails($billingAddress, $invoiceDetails);

            $vatId = $billingAddress->getVatId();
            if ($vatId) {
                $invoiceDetails->setCompanyName((string)$billingAddress->getCompany());
                $invoiceDetails->setTaxId((string)$billingAddress->getVatId());
                $invoiceDetails->setLegalForm(InPostInvoiceLegalForm::COMPANY->name);
            } else {
                $invoiceDetails->setLegalForm(InPostInvoiceLegalForm::PERSON->name);
            }
        }

        $customerNote = $order->getCustomerNote();
        if ($customerNote) {
            $invoiceDetails->setAdditionalInformation((string)$customerNote);
        }

        $inPostOrder->setInvoiceDetails($invoiceDetails);
    }

    private function transferInvoiceAddressDetails(
        OrderAddressInterface $address,
        InvoiceDetailsInterface $invoiceDetails
    ): void {
        $streetData = $address->getStreet() ?? [];

        $street = (isset($streetData[0]) && is_string($streetData[0])) ? (string)$streetData[0] : '';
        $building = (isset($streetData[1]) && is_string($streetData[1])) ? (string)$streetData[1] : '';
        $flat = (isset($streetData[2]) && is_string($streetData[2])) ? (string)$streetData[2] : '';

        $invoiceDetails->setStreet($street);
        $invoiceDetails->setBuilding($building);
        $invoiceDetails->setFlat($flat);
    }
}
