<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\DataTransfer\OrderToInPostOrderDataTransferInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Enum\InPostInvoiceLegalForm;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;

class OrderToInPostOrderInvoiceDetailsDataTransfer implements OrderToInPostOrderDataTransferInterface
{
    public function __construct(
        private readonly InvoiceDetailsInterfaceFactory $invoiceDetailsFactory,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository
    ) {
    }

    public function transfer(Order $order, OrderInterface $inPostOrder): void
    {
        $billingAddress = $order->getBillingAddress();
        $inPostPayOrder = $this->getInPostPayOrderByOrder($order);

        if (!$billingAddress instanceof OrderAddressInterface
            || $inPostPayOrder === null
            || !$inPostPayOrder->isOrderWithInvoice()
        ) {
            return;
        }

        $invoiceDetails = $inPostOrder->getInvoiceDetails();
        if ($invoiceDetails === null) {
            /** @var InvoiceDetailsInterface $invoiceDetails */
            $invoiceDetails = $this->invoiceDetailsFactory->create();
        }

        $invoiceDetails->setMail($inPostPayOrder->getInPostPayInvoiceEmail() ?? (string)$billingAddress->getEmail());
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

        $street = (isset($streetData[0])) ? (string)$streetData[0] : '';
        $building = (isset($streetData[1])) ? (string)$streetData[1] : '';
        $flat = (isset($streetData[2])) ? (string)$streetData[2] : '';

        $invoiceDetails->setStreet($street);
        $invoiceDetails->setBuilding($building);
        $invoiceDetails->setFlat($flat);
    }

    private function getInPostPayOrderByOrder(Order $order): ?InPostPayOrderInterface
    {
        try {
            $orderId = (int)(is_scalar($order->getEntityId()) ? $order->getEntityId() : null);

            return $this->inPostPayOrderRepository->getByOrderId($orderId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
