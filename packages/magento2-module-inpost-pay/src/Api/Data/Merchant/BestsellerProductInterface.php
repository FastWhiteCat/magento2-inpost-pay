<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\BestsellerQuantityInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\ProductAvailabilityInterface;

interface BestsellerProductInterface
{
    public const PRODUCT_ID = 'product_id';
    public const STATUS = 'status';
    public const EAN = 'ean';
    public const QR_CODE = 'qr_code';
    public const DEEP_LINK = 'deep_link';
    public const PRODUCT_AVAILABILITY = 'product_availability';
    public const PRODUCT_NAME = 'product_name';
    public const PRODUCT_DESCRIPTION = 'product_description';
    public const PRODUCT_IMAGE = 'product_image';
    public const ADDITIONAL_PRODUCT_IMAGES = 'additional_product_images';
    public const PRICE = 'price';
    public const CURRENCY = 'currency';
    public const QUANTITY = 'quantity';
    public const PRODUCT_LINK = 'product_link';
    public const PRODUCT_ATTRIBUTES = 'product_attributes';
    public const DEFAULT_CURRENCY = 'PLN';

    /**
     * @return string
     */
    public function getProductId(): string;

    /**
     * @param string $productId
     * @return void
     */
    public function setProductId(string $productId): void;

    /**
     * @return string|null
     */
    public function getStatus(): ?string;

    /**
     * @param string|null $status
     * @return void
     */
    public function setStatus(?string $status): void;

    /**
     * @return string|null
     */
    public function getEan(): ?string;

    /**
     * @param string|null $ean
     * @return void
     */
    public function setEan(?string $ean): void;

    /**
     * @return string|null
     */
    public function getQrCode(): ?string;

    /**
     * @param string|null $qrCode
     * @return void
     */
    public function setQrCode(?string $qrCode): void;

    /**
     * @return string|null
     */
    public function getDeepLink(): ?string;

    /**
     * @param string|null $deepLink
     * @return void
     */
    public function setDeepLink(?string $deepLink): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\ProductAvailabilityInterface|null
     */
    public function getProductAvailability(): ?ProductAvailabilityInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\ProductAvailabilityInterface|null $availability
     * @return void
     */
    public function setProductAvailability(?ProductAvailabilityInterface $availability): void;

    /**
     * @return string
     */
    public function getProductName(): string;

    /**
     * @param string $productName
     * @return void
     */
    public function setProductName(string $productName): void;

    /**
     * @return string
     */
    public function getProductDescription(): string;

    /**
     * @param string $productDescription
     * @return void
     */
    public function setProductDescription(string $productDescription): void;

    /**
     * @return string
     */
    public function getProductImage(): string;

    /**
     * @param string $productImage
     * @return void
     */
    public function setProductImage(string $productImage): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Product\AdditionalImageInterface[]
     */
    public function getAdditionalProductImages(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Product\AdditionalImageInterface[] $additionalImages
     * @return void
     */
    public function setAdditionalProductImages(array $additionalImages): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getPrice(): PriceInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $price
     * @return void
     */
    public function setPrice(PriceInterface $price): void;

    /**
     * @return string
     */
    public function getCurrency(): string;

    /**
     * @param string $currency
     * @return void
     */
    public function setCurrency(string $currency): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\BestsellerQuantityInterface
     */
    public function getQuantity(): BestsellerQuantityInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\BestsellerQuantityInterface $quantity
     * @return void
     */
    public function setQuantity(BestsellerQuantityInterface $quantity): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Product\ProductAttributeInterface[]
     */
    public function getProductAttributes(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Product\ProductAttributeInterface[] $productAttributes
     * @return void
     */
    public function setProductAttributes(array $productAttributes): void;

    /**
     * @return string
     */
    public function getProductLink(): string;

    /**
     * @param string $productLink
     * @return void
     */
    public function setProductLink(string $productLink): void;
}
