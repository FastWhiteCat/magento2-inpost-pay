<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Order;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Service\ApiConnector\BasketBindingDelete;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class PlaceOrderEventObserver implements ObserverInterface
{
    private ?InPostPayQuoteInterface $inPostPayQuote = null;

    public function __construct(
        private readonly BasketBindingDelete $basketBindingDelete,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly LoggerInterface $logger
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
            $orderId = is_scalar($order->getId()) ? (int)$order->getId() : null;
            if ($orderId === null) {
                $this->logger->error('Empty order ID.');
                return;
            }

            try {
                $inPostPayQuote = $this->getInPostPayQuoteByQuoteId($orderId);
                if ($inPostPayQuote && $inPostPayQuote->getBasketId() && $inPostPayQuote->getInPostPayQuoteId()) {
                    $inPostPayQuoteId = is_scalar($inPostPayQuote->getInPostPayQuoteId())
                        ? (int)$inPostPayQuote->getInPostPayQuoteId()
                        : null;
                    if ($inPostPayQuoteId === null) {
                        $this->logger->error('Empty InPost Quote ID.');
                        return;
                    }

                    $this->basketBindingDelete->execute($inPostPayQuote->getBasketId(), true);
                    $this->inPostPayQuoteRepository->deleteById($inPostPayQuoteId);
                }
            } catch (LocalizedException $e) {
                $errorMsg = 'Deleting order binding with InPost Pay was not successful.';
                $this->logger->error(sprintf('%s Reason: %s', $errorMsg, $e->getMessage()));
            }
        }
    }

    private function canSync(Order $order): bool
    {
        $quoteId = (int)(is_scalar($order->getQuoteId()) ? $order->getQuoteId() : null);
        $inPostPayOrder = $this->getInPostPayQuoteByQuoteId($quoteId);

        if (!$inPostPayOrder) {
            return false;
        }

        return true;
    }

    private function getInPostPayQuoteByQuoteId(int $quoteId): ?InPostPayQuoteInterface
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
}
