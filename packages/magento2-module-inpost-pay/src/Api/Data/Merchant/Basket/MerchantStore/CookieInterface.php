<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket\MerchantStore;

interface CookieInterface
{
    public const DOMAIN = 'domain';
    public const KEY = 'key';
    public const VALUE = 'value';
    public const PATH = 'path';
    public const EXPIRES = 'expires';
    public const SECURE = 'secure';
    public const HTTP_ONLY = 'http_only';
    public const SAME_SITE = 'save_site';
    public const PRIORITY = 'priority';
    public const MAX_AGE = 'max_age';

    /**
     * @return string
     */
    public function getDomain(): string;

    /**
     * @param string $domain
     * @return void
     */
    public function setDomain(string $domain): void;

    /**
     * @return string
     */
    public function getKey(): string;

    /**
     * @param string $key
     * @return void
     */
    public function setKey(string $key): void;

    /**
     * @return string
     */
    public function getValue(): string;

    /**
     * @param string $value
     * @return void
     */
    public function setValue(string $value): void;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @param string $path
     * @return void
     */
    public function setPath(string $path): void;

    /**
     * @return string|null
     */
    public function getExpires(): ?string;

    /**
     * @param string|null $expires
     * @return void
     */
    public function setExpires(?string $expires = null): void;

    /**
     * @return bool|null
     */
    public function isSecure(): ?bool;

    /**
     * @param bool|null $secure
     * @return void
     */
    public function setSecure(?bool $secure = false): void;

    /**
     * @return bool|null
     */
    public function isHttpOnly(): ?bool;

    /**
     * @param bool|null $httpOnly
     * @return void
     */
    public function setHttpOnly(?bool $httpOnly = true): void;

    /**
     * @return string|null
     */
    public function getSameSite(): ?string;

    /**
     * @param string|null $sameSite
     * @return void
     */
    public function setSameSite(?string $sameSite = 'LAX'): void;

    /**
     * @return string|null
     */
    public function getPriority(): ?string;

    /**
     * @param string|null $priority
     * @return void
     */
    public function setPriority(?string $priority = 'MEDIUM'): void;

    /**
     * @return int|null
     */
    public function getMaxAge(): ?int;

    /**
     * @param int|null $maxAge
     * @return void
     */
    public function setMaxAge(?int $maxAge = 0): void;
}
