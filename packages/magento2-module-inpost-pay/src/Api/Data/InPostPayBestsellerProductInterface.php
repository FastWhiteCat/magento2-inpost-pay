<?php
declare(strict_types=1);

namespace InPost\InPostPay\Api\Data;

interface InPostPayBestsellerProductInterface
{
    public const TABLE_NAME = 'inpost_pay_bestseller_product';
    public const ENTITY_NAME = 'inpost_pay_bestseller_product';
    public const BESTSELLER_PRODUCT_ID = 'bestseller_product_id';
    public const SKU = 'sku';
    public const WEBSITE_ID = 'website_id';
    public const IS_ENABLED = 'is_enabled';
    public const AVAILABLE_START_DATE = 'available_start_date';
    public const AVAILABLE_END_DATE = 'available_end_date';
    public const PRIORITY = 'priority';
    public const SYNCHRONIZED_AT = 'synchronized_at';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * @return int|null
     */
    public function getBestsellerProductId(): ?int;

    /**
     * @param int $bestsellerProductId
     * @return InPostPayBestsellerProductInterface
     */
    public function setBestsellerProductId(int $bestsellerProductId): InPostPayBestsellerProductInterface;

    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @param string $sku
     * @return InPostPayBestsellerProductInterface
     */
    public function setSku(string $sku): InPostPayBestsellerProductInterface;

    /**
     * @return int
     */
    public function getWebsiteId(): int;

    /**
     * @param int $websiteId
     * @return InPostPayBestsellerProductInterface
     */
    public function setWebsiteId(int $websiteId): InPostPayBestsellerProductInterface;

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @param bool $isEnabled
     * @return InPostPayBestsellerProductInterface
     */
    public function setIsEnabled(bool $isEnabled): InPostPayBestsellerProductInterface;

    /**
     * @return string|null
     */
    public function getAvailableStartDate(): ?string;

    /**
     * @param string|null $availableStartDate
     * @return InPostPayBestsellerProductInterface
     */
    public function setAvailableStartDate(?string $availableStartDate = null): InPostPayBestsellerProductInterface;

    /**
     * @return string|null
     */
    public function getAvailableEndDate(): ?string;

    /**
     * @param string|null $availableEndDate
     * @return InPostPayBestsellerProductInterface
     */
    public function setAvailableEndDate(?string $availableEndDate = null): InPostPayBestsellerProductInterface;

    /**
     * @return int
     */
    public function getPriority(): int;

    /**
     * @param int $priority
     * @return InPostPayBestsellerProductInterface
     */
    public function setPriority(int $priority): InPostPayBestsellerProductInterface;

    /**
     * @return string|null
     */
    public function getSynchronizedAt(): ?string;

    /**
     * @param string|null $synchronizedAt
     * @return InPostPayBestsellerProductInterface
     */
    public function setSynchronizedAt(?string $synchronizedAt = null): InPostPayBestsellerProductInterface;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;
}
