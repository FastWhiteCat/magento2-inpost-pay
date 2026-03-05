<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Quote;

use InPost\InPostPay\Observer\Quote\UpdateInPostBasketEventObserver;
use InPost\InPostPay\Service\Cart\CartService;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class QuoteTotalsRefreshOnRemoteAccess
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(Quote $quote): void
    {
        $quoteId = (int)(is_scalar($quote->getId()) ? $quote->getId() : null);
        $before = (float)($quote->getGrandTotal() ?? 0.0);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $after = (float)($quote->getGrandTotal() ?? 0.0);
        $this->logger->info(
            sprintf('Totals collected for quote %s (before=%s after=%s)', (string)$quoteId, $before, $after)
        );

        if ($before !== $after) {
            $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
            $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
            $this->cartRepository->save($quote);
            $this->logger->info('Quote saved after totals recollection (grand total changed)');
        }
    }
}
