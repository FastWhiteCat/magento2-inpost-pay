<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Quote;

use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Enum\InPostBasketStatus;
use InPost\InPostPay\Service\Cart\CartService;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\ChangeQuoteControlInterface;
use Magento\Quote\Model\Quote;

class AllowRemoteQuoteChangeByInPostPayPlugin
{
    public function __construct(
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository
    ) {
    }

    /**
     * @param ChangeQuoteControlInterface $subject
     * @param bool $result
     * @param CartInterface $quote
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsAllowed(ChangeQuoteControlInterface $subject, bool $result, CartInterface $quote): bool
    {
        if (!$result && $quote instanceof Quote && $this->isInPostPayQuote($quote)) {
            $result = true;
        }

        return $result;
    }

    private function isInPostPayQuote(Quote $quote): bool
    {
        try {
            $quoteId = (int)(is_scalar($quote->getId()) ? $quote->getId() : null);
            $inPostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);
        } catch (NoSuchEntityException | LocalizedException $e) {
            return false;
        }

        $allowRemoteAccess = $quote->getData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS);

        return $allowRemoteAccess && $inPostPayQuote->getStatus() === InPostBasketStatus::SUCCESS->value;
    }
}
