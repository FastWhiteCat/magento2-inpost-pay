<?php

declare(strict_types=1);

namespace InPost\InPostPay\Registry\Quote;

class DigitalQuoteAllowRegistry
{
    private bool $isCurrentlyProcessedDigitalQuoteAllowed = true;

    public function registerIfCurrentlyProcessedDigitalQuoteIsAllowed(bool $isAllowed): void
    {
        $this->isCurrentlyProcessedDigitalQuoteAllowed = $isAllowed;
    }

    public function isCurrentlyProcessedDigitalQuoteAllowed(): bool
    {
        return $this->isCurrentlyProcessedDigitalQuoteAllowed;
    }

    public function resetRegistry(): void
    {
        $this->isCurrentlyProcessedDigitalQuoteAllowed = true;
    }
}
