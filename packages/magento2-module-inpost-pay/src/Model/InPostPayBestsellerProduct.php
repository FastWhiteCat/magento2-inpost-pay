<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use Magento\Framework\Model\AbstractModel;

class InPostPayBestsellerProduct extends AbstractModel implements InPostPayBestsellerProductInterface
{
    protected $_eventPrefix = InPostPayBestsellerProductInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayBestsellerProductInterface::ENTITY_NAME;
    protected $_idFieldName = InPostPayBestsellerProductInterface::BESTSELLER_PRODUCT_ID;

    /**
     * @return void
     */
    public function _construct(): void
    {
        $this->_init(ResourceModel\InPostPayBestsellerProduct::class);
    }

    /**
     * @return int|null
     */
    public function getBestsellerProductId(): ?int
    {
        $id = ($this->hasData(self::BESTSELLER_PRODUCT_ID)) ? $this->getData(self::BESTSELLER_PRODUCT_ID) : null;

        return ($id && is_scalar($id)) ? (int)$id : null;
    }

    /**
     * @param int $bestsellerProductId
     * @return InPostPayBestsellerProductInterface
     */
    public function setBestsellerProductId(int $bestsellerProductId): InPostPayBestsellerProductInterface
    {
        return $this->setData(self::BESTSELLER_PRODUCT_ID, $bestsellerProductId);
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        $sku = $this->getData(self::SKU);

        return ($sku && is_scalar($sku)) ? (string)$sku : '';
    }

    /**
     * @param string $sku
     * @return InPostPayBestsellerProductInterface
     */
    public function setSku(string $sku): InPostPayBestsellerProductInterface
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * @return int
     */
    public function getWebsiteId(): int
    {
        $websiteId = $this->getData(self::WEBSITE_ID);

        return ($websiteId && is_scalar($websiteId)) ? (int)$websiteId : 0;
    }

    /**
     * @param int $websiteId
     * @return InPostPayBestsellerProductInterface
     */
    public function setWebsiteId(int $websiteId): InPostPayBestsellerProductInterface
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * @return string|null
     */
    public function getAvailableStartDate(): ?string
    {
        $date = ($this->hasData(self::AVAILABLE_START_DATE)) ? $this->getData(self::AVAILABLE_START_DATE) : null;

        return ($date && is_scalar($date)) ? (string)$date : null;
    }

    /**
     * @param string|null $availableStartDate
     * @return InPostPayBestsellerProductInterface
     */
    public function setAvailableStartDate(?string $availableStartDate = null): InPostPayBestsellerProductInterface
    {
        return $this->setData(self::AVAILABLE_START_DATE, $availableStartDate);
    }

    /**
     * @return string|null
     */
    public function getAvailableEndDate(): ?string
    {
        $date = ($this->hasData(self::AVAILABLE_END_DATE)) ? $this->getData(self::AVAILABLE_END_DATE) : null;

        return ($date && is_scalar($date)) ? (string)$date : null;
    }

    /**
     * @param string|null $availableEndDate
     * @return InPostPayBestsellerProductInterface
     */
    public function setAvailableEndDate(?string $availableEndDate = null): InPostPayBestsellerProductInterface
    {
        return $this->setData(self::AVAILABLE_END_DATE, $availableEndDate);
    }

    /**
     * @return string|null
     */
    public function getSynchronizedAt(): ?string
    {
        $date = ($this->hasData(self::SYNCHRONIZED_AT)) ? $this->getData(self::SYNCHRONIZED_AT) : null;

        return ($date && is_scalar($date)) ? (string)$date : null;
    }

    /**
     * @param string|null $synchronizedAt
     * @return InPostPayBestsellerProductInterface
     */
    public function setSynchronizedAt(?string $synchronizedAt = null): InPostPayBestsellerProductInterface
    {
        return $this->setData(self::SYNCHRONIZED_AT, $synchronizedAt);
    }

    /**
     * @return string|null
     */
    public function getQrCode(): ?string
    {
        $qrCode = ($this->hasData(self::QR_CODE)) ? $this->getData(self::QR_CODE) : null;

        return ($qrCode && is_scalar($qrCode)) ? (string)$qrCode : null;
    }

    /**
     * @param string|null $qrCode
     * @return InPostPayBestsellerProductInterface
     */
    public function setQrCode(?string $qrCode = null): InPostPayBestsellerProductInterface
    {
        return $this->setData(self::QR_CODE, $qrCode);
    }

    /**
     * @return string|null
     */
    public function getDeepLink(): ?string
    {
        $deepLink = ($this->hasData(self::DEEP_LINK)) ? $this->getData(self::DEEP_LINK) : null;

        return ($deepLink && is_scalar($deepLink)) ? (string)$deepLink : null;
    }

    /**
     * @param string|null $deepLink
     * @return InPostPayBestsellerProductInterface
     */
    public function setDeepLink(?string $deepLink = null): InPostPayBestsellerProductInterface
    {
        return $this->setData(self::DEEP_LINK, $deepLink);
    }

    /**
     * @return string|null
     */
    public function getInPostPayStatus(): ?string
    {
        $inPostPayStatus = ($this->hasData(self::INPOST_PAY_STATUS)) ? $this->getData(self::INPOST_PAY_STATUS) : null;

        return ($inPostPayStatus && is_scalar($inPostPayStatus)) ? (string)$inPostPayStatus : null;
    }

    /**
     * @param string|null $inPostPayStatus
     * @return InPostPayBestsellerProductInterface
     */
    public function setInPostPayStatus(?string $inPostPayStatus = null): InPostPayBestsellerProductInterface
    {
        return $this->setData(self::INPOST_PAY_STATUS, $inPostPayStatus);
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        $error = ($this->hasData(self::ERROR)) ? $this->getData(self::ERROR) : null;

        return ($error && is_scalar($error)) ? (string)$error : null;
    }

    /**
     * @param string|null $error
     * @return InPostPayBestsellerProductInterface
     */
    public function setError(?string $error = null): InPostPayBestsellerProductInterface
    {
        return $this->setData(self::ERROR, $error);
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        $date = $this->getData(self::CREATED_AT);

        return ($date && is_scalar($date)) ? (string)$date : '';
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        $date = $this->getData(self::UPDATED_AT);

        return ($date && is_scalar($date)) ? (string)$date : '';
    }

    /**
     * Flag that is not saved in database. Used only to flag object not to be updated in this process.
     * @return bool
     */
    public function isSkipUpdateFlag(): bool
    {
        $skipUpdate = $this->getData(self::SKIP_UPDATE_FLAG);

        return (is_bool($skipUpdate)) ? $skipUpdate : false;
    }

    /**
     * Flag that is not saved in database. Used only to flag object not to be updated in this process.
     * @param bool $skipUpdate
     * @return void
     */
    public function setSkipUpdateFlag(bool $skipUpdate): void
    {
        $this->setData(self::SKIP_UPDATE_FLAG, $skipUpdate);
    }
}
