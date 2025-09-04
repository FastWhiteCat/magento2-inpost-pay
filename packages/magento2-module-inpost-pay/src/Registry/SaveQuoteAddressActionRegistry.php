<?php

declare(strict_types=1);

namespace InPost\InPostPay\Registry;

class SaveQuoteAddressActionRegistry
{
    private bool $isSaveQuoteAddressActionRegistered = false;

    /**
     * @return bool
     */
    public function isSaveQuoteAddressActionRegistered(): bool
    {
        return $this->isSaveQuoteAddressActionRegistered;
    }

    /**
     * @return void
     */
    public function registerSaveQuoteAddressAction(): void
    {
        $this->isSaveQuoteAddressActionRegistered = true;
    }

    /**
     * @return void
     */
    public function resetRegistry(): void
    {
        $this->isSaveQuoteAddressActionRegistered = false;
    }
}
