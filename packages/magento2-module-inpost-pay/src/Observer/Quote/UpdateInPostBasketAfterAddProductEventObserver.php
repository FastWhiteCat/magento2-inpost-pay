<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Quote;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Registry\SaveQuoteAddressActionRegistry;
use InPost\InPostPay\Service\UpdateInPostBasketEvent;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Checkout\Model\Cart;
use Magento\Quote\Model\Quote;

class UpdateInPostBasketAfterAddProductEventObserver implements ObserverInterface
{
    private ?InPostPayQuoteInterface $inPostPayQuote = null;

    public function __construct(
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly UpdateInPostBasketEvent $updateInPostBasketEvent,
        private readonly SaveQuoteAddressActionRegistry $saveQuoteAddressActionRegistry
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $cart = $observer->getEvent()->getData('cart');
        if ($cart instanceof Cart) {
            $quote = $cart->getQuote();
            if ($quote instanceof Quote && $this->canSync($quote)) {
                $this->updateInPostBasketEvent->execute($quote);
            }
        }
    }

    private function canSync(Quote $quote): bool
    {
        if ($quote->getData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG)) {
            return false;
        }

        if ($this->saveQuoteAddressActionRegistry->isSaveQuoteAddressActionRegistered()) {
            return false;
        }

        $quoteId = (int)(is_scalar($quote->getId()) ? $quote->getId() : null);
        $inPostPayQuote = $this->getInPostPayQuoteByQuoteId($quoteId);
        if (!$inPostPayQuote) {
            return false;
        }

        return $inPostPayQuote->getBrowserTrusted();
    }

    private function getInPostPayQuoteByQuoteId(int $quoteId): ?InPostPayQuoteInterface
    {
        if ($this->inPostPayQuote === null) {
            try {
                $inPostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);
            } catch (NoSuchEntityException | LocalizedException $e) {
                $inPostPayQuote = null;
            }

            $this->inPostPayQuote = $inPostPayQuote;
        }

        return $this->inPostPayQuote;
    }
}
