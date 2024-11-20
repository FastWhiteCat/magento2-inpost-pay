<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Cart\Session;

use Magento\Framework\Session\Config\ConfigInterface as SessionConfig;
use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CartSessionCookieProvider
{
    private const SESSION_COOKIE_NAME = 'PHPSESSID';

    /**
     * @param CookieReaderInterface $cookieReader
     * @param SessionConfig $sessionConfig
     */
    public function __construct(
        private readonly CookieReaderInterface $cookieReader,
        private readonly SessionConfig $sessionConfig
    ) {
    }

    /**
     * @return string|null
     */
    public function getCookieSession(): ?string
    {
        return $this->cookieReader->getCookie($this->getCookieSessionName());
    }

    /**
     * @return string
     */
    public function getCookieSessionName(): string
    {
        return self::SESSION_COOKIE_NAME;
    }

    /**
     * @return string
     */
    public function getCookieDomain(): string
    {
        return $this->sessionConfig->getCookieDomain();
    }

    /**
     * @return string
     */
    public function getCookiePath(): string
    {
        return $this->sessionConfig->getCookiePath();
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->sessionConfig->getCookieSecure();
    }

    /**
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->sessionConfig->getCookieHttpOnly();
    }

    /**
     * @return string
     */
    public function getSameSite(): string
    {
        return $this->sessionConfig->getCookieSameSite();
    }

    /**
     * @return int
     */
    public function getCookieLifetime(): int
    {
        return $this->sessionConfig->getCookieLifetime();
    }
}
