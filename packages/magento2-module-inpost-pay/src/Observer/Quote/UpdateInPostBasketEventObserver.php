<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Quote;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Model\Publisher\BasketCreateOrUpdatePublisher;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\ApiConnector\CreateOrUpdateBasket;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class UpdateInPostBasketEventObserver implements ObserverInterface
{
    public const SKIP_INPOST_PAY_SYNC_FLAG = 'skip_inpost_pay_sync';

    private ?InPostPayQuoteInterface $inPostPayQuote = null;

    public function __construct(
        private readonly IziApiConfigProvider $iziApiConfigProvider,
        private readonly CreateOrUpdateBasket $createOrUpdateBasket,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly BasketCreateOrUpdatePublisher $basketCreateOrUpdatePublisher,
        private readonly LoggerInterface $logger
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
            $quoteId = is_scalar($quote->getId()) ? (int)$quote->getId() : null;
            if ($quoteId == null) {
                $this->logger->error('Empty quote ID. Processing basket sync cannot be continued.');
                return;
            }

            try {
                $inPostPayQuote = $this->getInPostPayQuoteByQuoteId($quoteId);
                if ($inPostPayQuote) {
                    $this->handleBasketExport($quote, $inPostPayQuote);
                }
            } catch (LocalizedException $e) {
                $errorMsg = 'Basket synchronization with InPost Pay was not successful.';
                $this->logger->error(sprintf('%s Reason: %s', $errorMsg, $e->getMessage()));
            }
        }
    }

    private function canSync(Quote $quote): bool
    {
        if ($quote->getData(self::SKIP_INPOST_PAY_SYNC_FLAG)) {
            return false;
        }

        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProduct()->getIsVirtual()) {
                return false;
            }
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

    /**
     * @throws LocalizedException
     */
    private function handleBasketExport(Quote $quote, InPostPayQuoteInterface $inPostPayQuote): void
    {
        if ($this->iziApiConfigProvider->isAsyncBasketExportEnabled()) {
            $this->basketCreateOrUpdatePublisher->publish($inPostPayQuote);
        } else {
            $quoteId = is_scalar($quote->getId()) ? (int)$quote->getId() : null;
            $browserId = $inPostPayQuote->getBrowserId();
            $basketId = $inPostPayQuote->getBasketId();
            if ($browserId && $basketId) {
                $this->createOrUpdateBasket->execute($quote, $browserId, $basketId);
                $this->logger->debug(
                    sprintf('Basket for quote ID %s has been synchronously updated.', $quoteId)
                );
            } else {
                throw new LocalizedException(__('Quote with ID %1 is invalid.', $quoteId));
            }
        }
    }
}
