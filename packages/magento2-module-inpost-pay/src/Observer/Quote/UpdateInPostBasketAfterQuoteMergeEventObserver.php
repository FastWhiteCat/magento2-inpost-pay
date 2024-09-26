<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Quote;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Service\UpdateInPostBasketEvent;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class UpdateInPostBasketAfterQuoteMergeEventObserver implements ObserverInterface
{
    /**
     * @param InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository
     * @param UpdateInPostBasketEvent $updateInPostBasketEvent
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly UpdateInPostBasketEvent $updateInPostBasketEvent,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $customerQuote = $observer->getEvent()->getData('quote');

        if ($customerQuote instanceof Quote) {
            /** @phpstan-ignore-next-line */
            $customerQuoteId = (int)$customerQuote->getId();
            $customerBasket = $this->getInPostPayQuoteByQuoteId($customerQuoteId);

            if ($customerBasket) {
                $this->updateInPostBasketEvent->execute($customerQuote);
                $this->logger->debug(
                    sprintf('Quote ID:%s has been synchronized with InPost Pay API after merge.', $customerQuoteId)
                );
            }
        }
    }

    /**
     * @param int $quoteId
     * @return InPostPayQuoteInterface|null
     */
    private function getInPostPayQuoteByQuoteId(int $quoteId): ?InPostPayQuoteInterface
    {
        try {
            $inPostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);
        } catch (NoSuchEntityException $e) {
            $inPostPayQuote = null;
        }

        return $inPostPayQuote;
    }
}
