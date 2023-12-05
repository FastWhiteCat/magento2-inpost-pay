<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use InPost\InPostPay\Api\OrderProcessingStepInterface;
use InPost\InPostPay\Model\Dto\Order as OrderDto;
use InPost\InPostPay\Observer\Quote\UpdateInPostBasketEventObserver;
use InPost\InPostPay\Service\Cart\CartService;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class ReloadQuoteStep extends OrderProcessingStep implements OrderProcessingStepInterface
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    public function process(Quote $quote, OrderDto $orderDto): void
    {
        $quote = $this->cartRepository->get((int)$quote->getId());
        if ($quote instanceof Quote) {
            $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
            $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
        } else {
            throw new LocalizedException(__('Quote not found.'));
        }
    }
}
