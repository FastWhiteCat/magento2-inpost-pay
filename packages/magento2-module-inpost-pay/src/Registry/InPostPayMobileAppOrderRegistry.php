<?php

declare(strict_types=1);

namespace InPost\InPostPay\Registry;

class InPostPayMobileAppOrderRegistry
{
    private array $mobileAppQuotes = [];

    /**
     * Register a quote as being processed via mobile app
     *
     * @param int $quoteId
     * @return void
     */
    public function registerMobileAppQuote(int $quoteId): void
    {
        $this->mobileAppQuotes[$quoteId] = true;
    }

    /**
     * Check if a quote is registered as being processed via mobile app
     *
     * @param int $quoteId
     * @return bool
     */
    public function isMobileAppQuote(int $quoteId): bool
    {
        return isset($this->mobileAppQuotes[$quoteId]) && $this->mobileAppQuotes[$quoteId] === true;
    }

    /**
     * Unregister a quote from being processed via mobile app
     *
     * @param int $quoteId
     * @return void
     */
    public function unregisterMobileAppQuote(int $quoteId): void
    {
        unset($this->mobileAppQuotes[$quoteId]);
    }
}
