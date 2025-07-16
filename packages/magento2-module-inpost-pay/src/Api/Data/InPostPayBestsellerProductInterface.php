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
    public const AVAILABLE_START_DATE = 'available_start_date';
    public const AVAILABLE_END_DATE = 'available_end_date';
    public const SYNCHRONIZED_AT = 'synchronized_at';
    public const QR_CODE = 'qr_code';
    public const DEEP_LINK = 'deep_link';
    public const INPOST_PAY_STATUS = 'inpost_pay_status';
    public const ERROR = 'error';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const SKIP_UPDATE_FLAG = 'skip_update_flag';

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
     * @return string|null
     */
    public function getSynchronizedAt(): ?string;

    /**
     * @param string|null $synchronizedAt
     * @return InPostPayBestsellerProductInterface
     */
    public function setSynchronizedAt(?string $synchronizedAt = null): InPostPayBestsellerProductInterface;

    /**
     * @return string|null
     */
    public function getQrCode(): ?string;

    /**
     * @param string|null $qrCode
     * @return InPostPayBestsellerProductInterface
     */
    public function setQrCode(?string $qrCode = null): InPostPayBestsellerProductInterface;

    /**
     * @return string|null
     */
    public function getDeepLink(): ?string;

    /**
     * @param string|null $deepLink
     * @return InPostPayBestsellerProductInterface
     */
    public function setDeepLink(?string $deepLink = null): InPostPayBestsellerProductInterface;

    /**
     * @return string|null
     */
    public function getInPostPayStatus(): ?string;

    /**
     * @param string|null $inPostPayStatus
     * @return InPostPayBestsellerProductInterface
     */
    public function setInPostPayStatus(?string $inPostPayStatus = null): InPostPayBestsellerProductInterface;

    /**
     * @return string|null
     */
    public function getError(): ?string;

    /**
     * @param string|null $error
     * @return InPostPayBestsellerProductInterface
     */
    public function setError(?string $error = null): InPostPayBestsellerProductInterface;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @return bool
     */
    public function isSkipUpdateFlag(): bool;

    /**
     * @param bool $skipUpdate
     * @return void
     */
    public function setSkipUpdateFlag(bool $skipUpdate): void;
}
