<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\Product\QuantityInterface;

interface ProductInterface
{
    public const PRODUCT_ID = 'product_id';
    public const PRODUCT_CATEGORY = 'product_category';
    public const EAN = 'ean';
    public const PRODUCT_NAME = 'product_name';
    public const PRODUCT_DESCRIPTION = 'product_description';
    public const PRODUCT_LINK = 'product_link';
    public const PRODUCT_TYPE = 'product_type';
    public const PRODUCT_IMAGE = 'product_image';
    public const BASE_PRICE = 'base_price';
    public const PROMO_PRICE = 'promo_price';
    public const LOWEST_PRICE = 'lowest_price';
    public const QUANTITY = 'quantity';
    public const PRODUCT_ATTRIBUTES = 'product_attributes';
    public const DELIVERY_PRODUCT = 'delivery_product';
    public const DELIVERY_RELATED_PRODUCTS = 'delivery_related_products';
    public const ADDITIONAL_PRODUCT_IMAGES = 'additional_product_images';

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
     * @return string
     */
    public function getProductCategory(): string;

    /**
     * @param string $productCategory
     * @return void
     */
    public function setProductCategory(string $productCategory): void;

    /**
     * @return string
     */
    public function getEan(): string;

    /**
     * @param string $ean
     * @return void
     */
    public function setEan(string $ean): void;

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
    public function getProductLink(): string;

    /**
     * @param string $productLink
     * @return void
     */
    public function setProductLink(string $productLink): void;

    /**
     * @return string|null
     */
    public function getProductType(): ?string;

    /**
     * @param string|null $productType
     * @return void
     */
    public function setProductType(?string $productType): void;

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
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getBasePrice(): PriceInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $basePrice
     * @return void
     */
    public function setBasePrice(PriceInterface $basePrice): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface|null
     */
    public function getLowestPrice(): ?PriceInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface|null $lowestPrice
     * @return void
     */
    public function setLowestPrice(?PriceInterface $lowestPrice): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface|null
     */
    public function getPromoPrice(): ?PriceInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface|null $promoPrice
     * @return void
     */
    public function setPromoPrice(?PriceInterface $promoPrice): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Product\QuantityInterface
     */
    public function getQuantity(): QuantityInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Product\QuantityInterface $quantity
     * @return void
     */
    public function setQuantity(QuantityInterface $quantity): void;

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
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Product\DeliveryProductInterface[]
     */
    public function getDeliveryProduct(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Product\DeliveryProductInterface[] $deliveryProduct
     * @return void
     */
    public function setDeliveryProduct(array $deliveryProduct): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Product\DeliveryProductInterface[]|null
     */
    public function getDeliveryRelatedProducts(): ?array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Product\DeliveryProductInterface[] $deliveryRelatedProducts
     * @return void
     */
    public function setDeliveryRelatedProducts(array $deliveryRelatedProducts): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Product\AdditionalImageInterface[]
     */
    public function getAdditionalProductImages(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Product\AdditionalImageInterface[] $additionalProductImages
     * @return void
     */
    public function setAdditionalProductImages(array $additionalProductImages): void;
}
