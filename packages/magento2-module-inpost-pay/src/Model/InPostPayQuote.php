<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Enum\InPostBasketStatus;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

class InPostPayQuote extends AbstractModel implements InPostPayQuoteInterface
{
    protected $_eventPrefix = InPostPayQuoteInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayQuoteInterface::ENTITY_NAME;

    public function _construct(): void
    {
        $this->_init(ResourceModel\InPostPayQuote::class);
    }

    public function getInPostPayQuoteId(): ?int
    {
        $id = ($this->hasData(self::INPOST_PAY_QUOTE_ID)) ? $this->getData(self::INPOST_PAY_QUOTE_ID) : null;

        return ($id && is_scalar($id)) ? (int)$id : null;
    }

    public function setInPostPayQuoteId(int $inPostPayQuoteId): InPostPayQuoteInterface
    {
        return $this->setData(self::INPOST_PAY_QUOTE_ID, $inPostPayQuoteId);
    }

    public function getQuoteId(): int
    {
        $id = ($this->hasData(self::QUOTE_ID)) ? $this->getData(self::QUOTE_ID) : null;

        if ($id && is_scalar($id)) {
            return (int)$id;
        }

        throw new LocalizedException(__('Invalid InPost Pay Quote ID value.'));
    }

    public function setQuoteId(int $quoteId): InPostPayQuoteInterface
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    public function getBasketId(): string
    {
        $id = ($this->hasData(self::BASKET_ID)) ? $this->getData(self::BASKET_ID) : null;

        if ($id && is_scalar($id)) {
            return (string)$id;
        }

        throw new LocalizedException(__('Invalid Basket ID value.'));
    }

    public function setBasketId(string $basketId): InPostPayQuoteInterface
    {
        return $this->setData(self::BASKET_ID, $basketId);
    }

    public function getBasketBindingApiKey(): ?string
    {
        if ($this->hasData(self::BASKET_BINDING_API_KEY)) {
            $basketBindingApiKey = $this->getData(self::BASKET_BINDING_API_KEY);

            $basketBindingApiKey = is_scalar($basketBindingApiKey) ? (string)$basketBindingApiKey : null;
        };

        return $basketBindingApiKey ?? null;
    }

    public function setBasketBindingApiKey(?string $basketBindingApiKey): InPostPayQuoteInterface
    {
        return $this->setData(self::BASKET_BINDING_API_KEY, $basketBindingApiKey);
    }

    public function getInpostBasketId(): ?string
    {
        $id = ($this->hasData(self::INPOST_BASKET_ID)) ? $this->getData(self::INPOST_BASKET_ID) : null;

        return ($id && is_scalar($id)) ? (string)$id : null;
    }

    public function setInpostBasketId(string $inpostBasketId): InPostPayQuoteInterface
    {
        return $this->setData(self::INPOST_BASKET_ID, $inpostBasketId);
    }

    public function getStatus(): string
    {
        $status = ($this->hasData(self::STATUS)) ? $this->getData(self::STATUS) : null;

        return ($status && is_scalar($status)) ? (string)$status : InPostBasketStatus::PENDING->value;
    }

    public function setStatus(string $status): InPostPayQuoteInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getPhone(): ?string
    {
        $phone = ($this->hasData(self::PHONE)) ? $this->getData(self::PHONE) : null;

        return ($phone && is_scalar($phone)) ? (string)$phone : null;
    }

    public function setPhone(string $phone): InPostPayQuoteInterface
    {
        return $this->setData(self::PHONE, $phone);
    }

    public function getCountryPrefix(): ?string
    {
        $countryPrefix = ($this->hasData(self::COUNTRY_PREFIX)) ? $this->getData(self::COUNTRY_PREFIX) : null;

        return ($countryPrefix && is_scalar($countryPrefix)) ? (string)$countryPrefix : null;
    }

    public function setCountryPrefix(string $countryPrefix): InPostPayQuoteInterface
    {
        return $this->setData(self::COUNTRY_PREFIX, $countryPrefix);
    }

    public function getMaskedPhoneNumber(): ?string
    {
        $maskedPhoneNumber = ($this->hasData(self::MASKED_PHONE_NUMBER))
            ? $this->getData(self::MASKED_PHONE_NUMBER)
            : null;

        return ($maskedPhoneNumber && is_scalar($maskedPhoneNumber)) ? (string)$maskedPhoneNumber : null;
    }

    public function setMaskedPhoneNumber(string $maskedPhoneNumber): InPostPayQuoteInterface
    {
        return $this->setData(self::MASKED_PHONE_NUMBER, $maskedPhoneNumber);
    }

    public function getBrowserTrusted(): bool
    {
        return (bool)$this->getData(self::BROWSER_TRUSTED);
    }

    public function setBrowserTrusted(bool $browserTrusted): InPostPayQuoteInterface
    {
        return $this->setData(self::BROWSER_TRUSTED, $browserTrusted);
    }

    public function getBrowserId(): ?string
    {
        $browserId = ($this->hasData(self::BROWSER_ID)) ? $this->getData(self::BROWSER_ID) : null;

        return ($browserId && is_scalar($browserId)) ? (string)$browserId : null;
    }

    public function setBrowserId(string $browserId): InPostPayQuoteInterface
    {
        return $this->setData(self::BROWSER_ID, $browserId);
    }

    public function getName(): ?string
    {
        $name = ($this->hasData(self::NAME)) ? $this->getData(self::NAME) : null;

        return ($name && is_scalar($name)) ? (string)$name : null;
    }

    public function setName(string $name): InPostPayQuoteInterface
    {
        return $this->setData(self::NAME, $name);
    }

    public function getSurname(): ?string
    {
        $surname = ($this->hasData(self::SURNAME)) ? $this->getData(self::SURNAME) : null;

        return ($surname && is_scalar($surname)) ? (string)$surname : null;
    }

    public function setSurname(string $surname): InPostPayQuoteInterface
    {
        return $this->setData(self::SURNAME, $surname);
    }

    public function getCreatedAt(): string
    {
        $createdAt = $this->getData(self::CREATED_AT);

        if ($createdAt && is_scalar($createdAt)) {
            return (string)$createdAt;
        }

        throw new LocalizedException(__('Invalid InPost Pay Quote created at value.'));
    }

    public function getUpdatedAt(): string
    {
        $updatedAt = $this->getData(self::UPDATED_AT);

        if ($updatedAt && is_scalar($updatedAt)) {
            return (string)$updatedAt;
        }

        throw new LocalizedException(__('Invalid InPost Pay Quote updated at value.'));
    }

    public function getCartVersion(): string
    {
        $cartVersion = $this->getData(self::CART_VERSION);

        return is_scalar($cartVersion) ? (string)$cartVersion : '';
    }

    public function setCartVersion(string $cartVersion): InPostPayQuoteInterface
    {
        return $this->setData(self::CART_VERSION, $cartVersion);
    }

    public function getGaClientId(): ?string
    {
        $gaClientId = $this->getData(self::GA_CLIENT_ID);

        return (is_scalar($gaClientId) && !empty($gaClientId)) ? (string)$gaClientId : null;
    }

    public function setGaClientId(?string $gaClientId): InPostPayQuoteInterface
    {
        return $this->setData(self::GA_CLIENT_ID, $gaClientId);
    }

    public function getFbclid(): ?string
    {
        $fbclid = $this->getData(self::FBCLID);

        return (is_scalar($fbclid) && !empty($fbclid)) ? (string)$fbclid : null;
    }

    public function setFbclid(?string $fbclid): InPostPayQuoteInterface
    {
        return $this->setData(self::FBCLID, $fbclid);
    }

    public function getGclid(): ?string
    {
        $gclid = $this->getData(self::GCLID);

        return (is_scalar($gclid) && !empty($gclid)) ? (string)$gclid : null;
    }

    public function setGclid(?string $gclid): InPostPayQuoteInterface
    {
        return $this->setData(self::GCLID, $gclid);
    }

    public function getSessionCookie(): ?string
    {
        $sessionCookie = $this->getData(self::SESSION_COOKIE);

        return is_scalar($sessionCookie) ? (string)$sessionCookie : null;
    }

    public function setSessionCookie(?string $sessionCookie): InPostPayQuoteInterface
    {
        return $this->setData(self::SESSION_COOKIE, $sessionCookie);
    }
}
