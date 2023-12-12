<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket;

interface BrowserInterface
{
    public const BROWSER_TRUSTED = 'browser_trusted';
    public const BROWSER_ID = 'browser_id';

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getBrowserTrusted(): bool;

    /**
     * @param bool $browserTrusted
     * @return void
     */
    public function setBrowserTrusted(bool $browserTrusted): void;

    /**
     * @return string
     */
    public function getBrowserId(): string;

    /**
     * @param string $browserId
     * @return void
     */
    public function setBrowserId(string $browserId): void;
}
