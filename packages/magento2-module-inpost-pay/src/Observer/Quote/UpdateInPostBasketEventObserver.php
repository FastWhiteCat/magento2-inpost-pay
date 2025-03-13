<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Quote;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Registry\SaveQuoteAddressActionRegistry;
use InPost\InPostPay\Service\UpdateInPostBasketEvent;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;

class UpdateInPostBasketEventObserver implements ObserverInterface
{
    public const SKIP_INPOST_PAY_SYNC_FLAG = 'skip_inpost_pay_sync';

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
        $quote = $observer->getEvent()->getData('quote');
        if ($quote instanceof Quote && $this->canSync($quote)) {
            $this->updateInPostBasketEvent->execute($quote);
        }
    }

    private function canSync(Quote $quote): bool
    {
        if ($quote->getData(self::SKIP_INPOST_PAY_SYNC_FLAG)) {
            return false;
        }

        if ($this->saveQuoteAddressActionRegistry->isSaveQuoteAddressActionRegistered()) {
            return false;
        }

        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProduct()->getTypeId() === Type::TYPE_BUNDLE
                && !$item->getItemId()
            ) {
                return false;
            }
        }

        $quote->setData(self::SKIP_INPOST_PAY_SYNC_FLAG, true);

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
