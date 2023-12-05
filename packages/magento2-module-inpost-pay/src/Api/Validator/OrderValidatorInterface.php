<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Validator;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Model\Dto\Order as DtoOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

interface OrderValidatorInterface
{
    /**
     * @param Quote $quote
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @param DtoOrder $orderDto
     * @return void
     * @throws LocalizedException
     */
    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, DtoOrder $orderDto): void;
}
