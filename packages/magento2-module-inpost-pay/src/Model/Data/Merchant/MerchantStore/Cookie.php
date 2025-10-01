<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\MerchantStore;

use InPost\InPostPay\Api\Data\Merchant\Basket\MerchantStore\CookieInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class Cookie extends DataObject implements CookieInterface, ExtensibleDataInterface
{

    /**
     * @return string
     */
    public function getDomain(): string
    {
        $domain = $this->getData(self::DOMAIN);

        return is_scalar($domain) ? (string)$domain : '';
    }

    /**
     * @param string $domain
     * @return void
     */
    public function setDomain(string $domain): void
    {
        $this->setData(self::DOMAIN, $domain);
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        $key = $this->getData(self::KEY);

        return is_scalar($key) ? (string)$key : '';
    }

    /**
     * @param string $key
     * @return void
     */
    public function setKey(string $key): void
    {
        $this->setData(self::KEY, $key);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        $value = $this->getData(self::VALUE);

        return is_scalar($value) ? (string)$value : '';
    }

    /**
     * @param string $value
     * @return void
     */
    public function setValue(string $value): void
    {
        $this->setData(self::VALUE, $value);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        $path = $this->getData(self::PATH);

        return is_scalar($path) ? (string)$path : '';
    }

    /**
     * @param string $path
     * @return void
     */
    public function setPath(string $path): void
    {
        $this->setData(self::PATH, $path);
    }

    /**
     * @return string|null
     */
    public function getExpires(): ?string
    {
        $expires = $this->getData(self::EXPIRES);

        return is_scalar($expires) ? (string)$expires : null;
    }

    /**
     * @param string|null $expires
     * @return void
     */
    public function setExpires(?string $expires = null): void
    {
        $this->setData(self::EXPIRES, $expires);
    }

    /**
     * @return bool|null
     */
    public function isSecure(): ?bool
    {
        $secure = $this->getData(self::SECURE);

        return is_scalar($secure) ? (bool)$secure : null;
    }

    /**
     * @param bool|null $secure
     * @return void
     */
    public function setSecure(?bool $secure = false): void
    {
        $this->setData(self::SECURE, $secure);
    }

    /**
     * @return bool|null
     */
    public function isHttpOnly(): ?bool
    {
        $httpOnly = $this->getData(self::HTTP_ONLY);

        return is_scalar($httpOnly) ? (bool)$httpOnly : null;
    }

    /**
     * @param bool|null $httpOnly
     * @return void
     */
    public function setHttpOnly(?bool $httpOnly = true): void
    {
        $this->setData(self::HTTP_ONLY, $httpOnly);
    }

    /**
     * @return string|null
     */
    public function getSameSite(): ?string
    {
        $sameSite = $this->getData(self::SAME_SITE);

        return is_scalar($sameSite) ? (string)$sameSite : null;
    }

    /**
     * @param string|null $sameSite
     * @return void
     */
    public function setSameSite(?string $sameSite = 'LAX'): void
    {
        $this->setData(self::SAME_SITE, $sameSite);
    }

    /**
     * @return string|null
     */
    public function getPriority(): ?string
    {
        $priority = $this->getData(self::PRIORITY);

        return is_scalar($priority) ? (string)$priority : null;
    }

    /**
     * @param string|null $priority
     * @return void
     */
    public function setPriority(?string $priority = 'MEDIUM'): void
    {
        $this->setData(self::PRIORITY, $priority);
    }

    /**
     * @return int|null
     */
    public function getMaxAge(): ?int
    {
        $maxAge = $this->getData(self::MAX_AGE);

        return is_scalar($maxAge) ? (int)$maxAge : null;
    }

    /**
     * @param int|null $maxAge
     * @return void
     */
    public function setMaxAge(?int $maxAge = 0): void
    {
        $this->setData(self::MAX_AGE, $maxAge);
    }
}
