<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
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

    public function getBasketId(): ?string
    {
        $id = ($this->hasData(self::BASKET_ID)) ? $this->getData(self::BASKET_ID) : null;

        if ($id && is_scalar($id)) {
            return (string)$id;
        }

        throw new LocalizedException(__('Invalid Baslet ID value.'));
    }

    public function setBasketId(string $basketId): InPostPayQuoteInterface
    {
        return $this->setData(self::BASKET_ID, $basketId);
    }

    public function getInpostBasketId(): ?string
    {
        $id = ($this->hasData(self::INPOST_BASKET_ID)) ? $this->getData(self::INPOST_BASKET_ID) : null;

        return ($id && is_scalar($id)) ? (string)$id : null;
    }

    public function setInpostBasketId(string $basketId): InPostPayQuoteInterface
    {
        return $this->setData(self::BASKET_ID, $basketId);
    }

    public function getStatus(): ?string
    {
        $status = ($this->hasData(self::STATUS)) ? $this->getData(self::STATUS) : null;

        return ($status && is_scalar($status)) ? (string)$status : null;
    }

    public function setStatus(string $status): InPostPayQuoteInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getPhoneNumber(): ?string
    {
        $phoneNumber = ($this->hasData(self::PHONE_NUMBER)) ? $this->getData(self::PHONE_NUMBER) : null;

        return ($phoneNumber && is_scalar($phoneNumber)) ? (string)$phoneNumber : null;
    }

    public function setPhoneNumber(string $phoneNumber): InPostPayQuoteInterface
    {
        return $this->setData(self::PHONE_NUMBER, $phoneNumber);
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

    public function getBrowserTrusted(): ?bool
    {
        $browserTrusted = ($this->hasData(self::BROWSER_TRUSTED)) ? $this->getData(self::BROWSER_TRUSTED) : null;

        return ($browserTrusted && is_scalar($browserTrusted)) ? (bool)$browserTrusted : null;
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
        return $this->setData(self::STATUS, $surname);
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
}
