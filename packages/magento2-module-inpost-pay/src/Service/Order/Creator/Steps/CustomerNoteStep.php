<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use InPost\InPostPay\Api\OrderProcessingStepInterface;
use InPost\InPostPay\Model\Dto\Order as OrderDto;
use Magento\Quote\Model\Quote;

class CustomerNoteStep extends OrderProcessingStep implements OrderProcessingStepInterface
{
    public function process(Quote $quote, OrderDto $orderDto): void
    {
        $customerNotes = [];
        if ($orderDto->getOrderDetails()->getOrderComments()) {
            $customerNotes[] = $orderDto->getOrderDetails()->getOrderComments();
        }
        $invoiceDetails = $orderDto->getInvoiceDetails();
        if ($invoiceDetails && $invoiceDetails->getAdditionalInformation()) {
            $customerNotes[] = $invoiceDetails->getAdditionalInformation();
        }

        $customerNote = implode('. ', $customerNotes);
        $quote->setCustomerNote($customerNote);

        $this->createLog(
            sprintf(
                'Customer Note has been applied to quote ID: %s. Content: %s',
                (int)$quote->getId(),
                $customerNote
            )
        );
    }
}
