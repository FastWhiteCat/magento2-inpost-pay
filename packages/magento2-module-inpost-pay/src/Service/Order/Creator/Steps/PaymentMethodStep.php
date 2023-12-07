<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use InPost\InPostPay\Api\OrderProcessingStepInterface;
use InPost\InPostPay\Model\Dto\Order as OrderDto;
use InPost\InPostPay\Observer\Quote\UpdateInPostBasketEventObserver;
use InPost\InPostPay\Service\Cart\CartService;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class PaymentMethodStep extends OrderProcessingStep implements OrderProcessingStepInterface
{
    public const INPOST_PAY_PAYMENT_METHOD_CODE = 'inpost_pay';

    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    public function process(Quote $quote, OrderDto $orderDto): void
    {
        $payment = $quote->getPayment();
        $payment->setMethod(self::INPOST_PAY_PAYMENT_METHOD_CODE);
        $quote->setPayment($payment);
        $this->addCustomerNote($quote, $orderDto);
        // @phpstan-ignore-next-line
        $quote->setInventoryProcessed(false);
        $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
        $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
        $this->cartRepository->save($quote);

        $this->createLog(
            sprintf(
                'Payment method %s has been applied to quote ID: %s',
                self::INPOST_PAY_PAYMENT_METHOD_CODE,
                (int)$quote->getId()
            )
        );
    }

    public function addCustomerNote(Quote $quote, OrderDto $orderDto): void
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
