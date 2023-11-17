<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Converter;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

interface QuoteToBasketDataConverterInterface
{
    /**
     * @param Quote $quote
     * @return array
     * @throws LocalizedException
     */
    public function convert(Quote $quote): array;
}
