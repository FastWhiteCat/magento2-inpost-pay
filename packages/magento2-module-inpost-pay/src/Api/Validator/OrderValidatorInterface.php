<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Validator;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

interface OrderValidatorInterface
{
    /**
     * @param Quote $quote
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @param OrderInterface $inPostOrder
     * @return void
     * @throws LocalizedException
     */
    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, OrderInterface $inPostOrder): void;
}
