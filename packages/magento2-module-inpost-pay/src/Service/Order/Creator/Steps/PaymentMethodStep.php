<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use InPost\InPostPay\Api\OrderProcessingStepInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Observer\Quote\UpdateInPostBasketEventObserver;
use InPost\InPostPay\Service\Cart\CartService;
use InPost\InPostPay\Validator\Order\BasketPriceValidator;
use Magento\Framework\Exception\LocalizedException;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Exception\QuoteChangedDuringOrderProcessingException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class PaymentMethodStep extends OrderProcessingStep implements OrderProcessingStepInterface
{
    public const INPOST_PAY_PAYMENT_METHOD_CODE = 'inpost_pay';

    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly BasketPriceValidator $basketPriceValidator,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    public function process(Quote $quote, OrderInterface $inPostOrder): void
    {
        $payment = $quote->getPayment();
        $payment->setMethod(self::INPOST_PAY_PAYMENT_METHOD_CODE);
        $quote->setPayment($payment);
        $this->addCustomerNote($quote, $inPostOrder);
        // @phpstan-ignore-next-line
        $quote->setInventoryProcessed(false);
        $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
        $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
        $this->cartRepository->save($quote);

        $this->createLog(
            sprintf(
                'Payment method %s has been applied to quote ID: %s',
                self::INPOST_PAY_PAYMENT_METHOD_CODE,
                (int)(is_scalar($quote->getId()) ? $quote->getId() : null)
            )
        );

        $this->validateBasketTotal($quote, $inPostOrder);
    }

    public function addCustomerNote(Quote $quote, OrderInterface $inPostOrder): void
    {
        $customerNotes = [];
        if ($inPostOrder->getOrderDetails()->getOrderComments()) {
            $customerNotes[] = $inPostOrder->getOrderDetails()->getOrderComments();
        }
        $invoiceDetails = $inPostOrder->getInvoiceDetails();
        if ($invoiceDetails && $invoiceDetails->getAdditionalInformation()) {
            $customerNotes[] = $invoiceDetails->getAdditionalInformation();
        }

        $customerNote = implode('. ', $customerNotes);
        $quote->setCustomerNote($customerNote);

        $this->createLog(
            sprintf(
                'Customer Note has been applied to quote ID: %s. Content: %s',
                (int)(is_scalar($quote->getId()) ? $quote->getId() : null),
                $customerNote
            )
        );
    }

    /**
     * @param Quote $quote
     * @param OrderInterface $inPostOrder
     * @return void
     * @throws QuoteChangedDuringOrderProcessingException
     */
    private function validateBasketTotal(Quote $quote, OrderInterface $inPostOrder): void
    {
        $cartId = is_scalar($quote->getId()) ? (int)$quote->getId() : 0;

        try {
            /** @var Quote $reloadedQuote */
            $reloadedQuote = $this->cartRepository->get($cartId);
            $inPostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($cartId);
            $this->basketPriceValidator->validate($reloadedQuote, $inPostPayQuote, $inPostOrder);
        } catch (LocalizedException $e) {
            throw new QuoteChangedDuringOrderProcessingException();
        }
    }
}
