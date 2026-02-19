<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\DataTransfer;

use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

interface QuoteToBasketDataTransferInterface
{
    /**
     * @param Quote $quote
     * @param BasketInterface $basket
     * @return void
     * @throws LocalizedException
     */
    public function transfer(Quote $quote, BasketInterface $basket): void;
}
