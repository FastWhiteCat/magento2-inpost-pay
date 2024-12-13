<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Model\Publisher\BasketCreateOrUpdatePublisher;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\ApiConnector\CreateOrUpdateBasket;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class UpdateInPostBasketEvent
{
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
     * @param Quote $quote
     * @return void
     */
    public function execute(Quote $quote): void
    {
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
                $this->createOrUpdateBasket->execute($quote, $basketId);
                $this->logger->debug(
                    sprintf('Basket for quote ID %s has been synchronously updated.', $quoteId)
                );
            } else {
                throw new LocalizedException(__('Quote with ID %1 is invalid.', $quoteId));
            }
        }
    }
}
