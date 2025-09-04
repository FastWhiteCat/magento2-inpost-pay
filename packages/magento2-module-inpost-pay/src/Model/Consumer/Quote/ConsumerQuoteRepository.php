<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Consumer\Quote;

use Magento\Quote\Model\QuoteRepository;

class ConsumerQuoteRepository extends QuoteRepository
{
    /**
     * @return void
     */
    public function cleanCachedQuotes(): void
    {
        $this->quotesById = [];
        $this->quotesByCustomerId = [];
    }
}
