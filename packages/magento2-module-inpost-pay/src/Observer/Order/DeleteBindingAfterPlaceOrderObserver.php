<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Order;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Service\ApiConnector\BasketBindingDelete;
use InPost\InPostPay\Service\Cart\BasketBindingApiKeyCookieService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use InPost\InPostPay\Registry\Order\Creation\InPostPayOrderCreationRegistry;
use Psr\Log\LoggerInterface;

class DeleteBindingAfterPlaceOrderObserver implements ObserverInterface
{
    private ?InPostPayQuoteInterface $inPostPayQuote = null;

    public function __construct(
        protected readonly BasketBindingDelete $basketBindingDelete,
        protected readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        protected readonly BasketBindingApiKeyCookieService $basketBindingApiKeyCookieService,
        protected readonly InPostPayOrderCreationRegistry $orderCreationRegistry,
        protected readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getData('order');
        if ($order instanceof Order && $this->canSync($order)) {
            $quoteId = is_scalar($order->getQuoteId()) ? (int)$order->getQuoteId() : null;

            if ($quoteId === null) {
                $this->logger->error('Empty quote ID. Skipping basket binding delete procedure.');
                return;
            }

            try {
                $inPostPayQuote = $this->getInPostPayQuoteByQuoteId($quoteId);
                if ($inPostPayQuote
                    && $inPostPayQuote->getBasketId()
                    && $inPostPayQuoteId = $inPostPayQuote->getInPostPayQuoteId()
                ) {
                    $this->deleteBasketFromInPostPayApi($inPostPayQuote);
                    $this->inPostPayQuoteRepository->deleteById($inPostPayQuoteId);
                    $this->basketBindingApiKeyCookieService->deleteBasketBindingKeyCookie();
                }
            } catch (LocalizedException $e) {
                $errorMsg = 'Deleting order binding with InPost Pay was not successful.';
                $this->logger->error(sprintf('%s Reason: %s', $errorMsg, $e->getMessage()));
            }
        }
    }

    protected function canSync(Order $order): bool
    {
        $quoteId = (int)(is_scalar($order->getQuoteId()) ? $order->getQuoteId() : null);
        $inPostPayQuote = $this->getInPostPayQuoteByQuoteId($quoteId);

        if (!$inPostPayQuote) {
            return false;
        }

        return true;
    }

    protected function getInPostPayQuoteByQuoteId(int $quoteId): ?InPostPayQuoteInterface
    {
        if ($this->inPostPayQuote === null) {
            try {
                $inPostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);
            } catch (NoSuchEntityException) {
                $inPostPayQuote = null;
            }

            $this->inPostPayQuote = $inPostPayQuote;
        }

        return $this->inPostPayQuote;
    }

    protected function deleteBasketFromInPostPayApi(InPostPayQuoteInterface $inPostPayQuote): void
    {
        $registeredBasketId = $this->orderCreationRegistry->registry();
        if ($registeredBasketId && $registeredBasketId === $inPostPayQuote->getBasketId()) {
            $this->logger->debug(
                sprintf(
                    'Skipping BasketBindingDelete for Basket ID:%s InPost Pay triggered order creation.',
                    $registeredBasketId
                )
            );
        } else {
            try {
                $this->basketBindingDelete->execute($inPostPayQuote->getBasketId(), true);
            } catch (LocalizedException $e) {
                $this->logger->error(
                    sprintf(
                        'Could not delete Basket ID:%s from InPost Pay API. Reason: %s.',
                        $registeredBasketId,
                        $e->getMessage()
                    )
                );
            }
        }
    }
}
