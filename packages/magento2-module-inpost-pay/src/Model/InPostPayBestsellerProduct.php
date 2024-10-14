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
     * @return bool
     */
    public function isEnabled(): bool
    {
        $isEnabled = $this->getData(self::IS_ENABLED);

        return ($isEnabled && is_scalar($isEnabled)) ? (bool)$isEnabled : false;
    }

    /**
     * @param bool $isEnabled
     * @return InPostPayBestsellerProductInterface
     */
    public function setIsEnabled(bool $isEnabled): InPostPayBestsellerProductInterface
    {
        return $this->setData(self::IS_ENABLED, $isEnabled);
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
     * @return int
     */
    public function getPriority(): int
    {
        $priority = $this->getData(self::PRIORITY);

        return ($priority && is_scalar($priority)) ? (int)$priority : 1;
    }

    /**
     * @param int $priority
     * @return InPostPayBestsellerProductInterface
     */
    public function setPriority(int $priority): InPostPayBestsellerProductInterface
    {
        return $this->setData(self::PRIORITY, $priority);
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
}
