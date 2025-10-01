<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\MerchantStore\CookieInterface;

interface MerchantStoreInterface
{
    public const URL = 'url';
    public const COOKIES = 'cookies';

    /**
     * @return string
     */
    public function getUrl(): string;

    /**
     * @param string $url
     * @return void
     */
    public function setUrl(string $url): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\MerchantStore\CookieInterface[]
     */
    public function getCookies(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\MerchantStore\CookieInterface[] $cookies
     * @return void
     */
    public function setCookies(array $cookies): void;
}
