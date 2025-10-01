<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\Product\ProductAttributeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Product\QuantityInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\Product\QuantityInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class Product extends DataObject implements ProductInterface, ExtensibleDataInterface
{
    /**
     * @param QuantityInterfaceFactory $quantityFactory
     * @param PriceInterfaceFactory $priceFactory
     * @param array $data
     */
    public function __construct(
        private readonly QuantityInterfaceFactory $quantityFactory,
        private readonly PriceInterfaceFactory $priceFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        $productId = $this->getData(self::PRODUCT_ID);

        return is_scalar($productId) ? (string)$productId : '';
    }

    /**
     * @param string $productId
     * @return void
     */
    public function setProductId(string $productId): void
    {
        $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @return string
     */
    public function getProductCategory(): string
    {
        $productCategory = $this->getData(self::PRODUCT_CATEGORY);

        return is_scalar($productCategory) ? (string)$productCategory : '';
    }

    /**
     * @param string $productCategory
     * @return void
     */
    public function setProductCategory(string $productCategory): void
    {
        $this->setData(self::PRODUCT_CATEGORY, $productCategory);
    }

    /**
     * @return string
     */
    public function getEan(): string
    {
        $ean = $this->getData(self::EAN);

        return is_scalar($ean) ? (string)$ean : '';
    }

    /**
     * @param string $ean
     * @return void
     */
    public function setEan(string $ean): void
    {
        $this->setData(self::EAN, $ean);
    }

    /**
     * @return string
     */
    public function getProductName(): string
    {
        $productName = $this->getData(self::PRODUCT_NAME);

        return is_scalar($productName) ? (string)$productName : '';
    }

    /**
     * @param string $productName
     * @return void
     */
    public function setProductName(string $productName): void
    {
        $this->setData(self::PRODUCT_NAME, $productName);
    }

    /**
     * @return string
     */
    public function getProductDescription(): string
    {
        $productDescription = $this->getData(self::PRODUCT_DESCRIPTION);

        return is_scalar($productDescription) ? (string)$productDescription : '';
    }

    /**
     * @param string $productDescription
     * @return void
     */
    public function setProductDescription(string $productDescription): void
    {
        $this->setData(self::PRODUCT_DESCRIPTION, $productDescription);
    }

    /**
     * @return string
     */
    public function getProductLink(): string
    {
        $productLink = $this->getData(self::PRODUCT_LINK);

        return is_scalar($productLink) ? (string)$productLink : '';
    }

    /**
     * @param string $productLink
     * @return void
     */
    public function setProductLink(string $productLink): void
    {
        $this->setData(self::PRODUCT_LINK, $productLink);
    }

    /**
     * @return string|null
     */
    public function getProductType(): ?string
    {
        $productType = $this->hasData(self::PRODUCT_TYPE) ? $this->getData(self::PRODUCT_TYPE) : null;

        return is_scalar($productType) ? (string)$productType : null;
    }

    /**
     * @param string|null $productType
     * @return void
     */
    public function setProductType(?string $productType): void
    {
        $this->setData(self::PRODUCT_TYPE, $productType);
    }

    /**
     * @return string
     */
    public function getProductImage(): string
    {
        $productImage = $this->getData(self::PRODUCT_IMAGE);

        return is_scalar($productImage) ? (string)$productImage : '';
    }

    /**
     * @param string $productImage
     * @return void
     */
    public function setProductImage(string $productImage): void
    {
        $this->setData(self::PRODUCT_IMAGE, $productImage);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getBasePrice(): PriceInterface
    {
        $basePrice = $this->getData(self::BASE_PRICE);

        if ($basePrice instanceof PriceInterface) {
            return $basePrice;
        }

        return $this->priceFactory->create();
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $basePrice
     * @return void
     */
    public function setBasePrice(PriceInterface $basePrice): void
    {
        $this->setData(self::BASE_PRICE, $basePrice);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface|null
     */
    public function getLowestPrice(): ?PriceInterface
    {
        $lowestPrice = $this->getData(self::LOWEST_PRICE);

        if ($lowestPrice instanceof PriceInterface) {
            return $lowestPrice;
        }

        return null;
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface|null $lowestPrice
     * @return void
     */
    public function setLowestPrice(?PriceInterface $lowestPrice): void
    {
        $this->setData(self::LOWEST_PRICE, $lowestPrice);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface|null
     */
    public function getPromoPrice(): ?PriceInterface
    {
        $promoPrice = $this->getData(self::PROMO_PRICE);

        if ($promoPrice instanceof PriceInterface) {
            return $promoPrice;
        }

        return null;
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface|null $promoPrice
     * @return void
     */
    public function setPromoPrice(?PriceInterface $promoPrice): void
    {
        $this->setData(self::PROMO_PRICE, $promoPrice);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Product\QuantityInterface
     */
    public function getQuantity(): QuantityInterface
    {
        $quantity = $this->getData(self::QUANTITY);

        if ($quantity instanceof QuantityInterface) {
            return $quantity;
        }

        return $this->quantityFactory->create();
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Product\QuantityInterface $quantity
     * @return void
     */
    public function setQuantity(QuantityInterface $quantity): void
    {
        $this->setData(self::QUANTITY, $quantity);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Product\ProductAttributeInterface[]
     */
    public function getProductAttributes(): array
    {
        $productAttributes = $this->getData(self::PRODUCT_ATTRIBUTES);

        return is_array($productAttributes) ? $productAttributes : [];
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Product\ProductAttributeInterface[] $productAttributes
     * @return void
     */
    public function setProductAttributes(array $productAttributes): void
    {
        $this->setData(self::PRODUCT_ATTRIBUTES, $productAttributes);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Product\DeliveryProductInterface[]
     */
    public function getDeliveryProduct(): array
    {
        $deliveryProduct = $this->getData(self::DELIVERY_PRODUCT);

        return is_array($deliveryProduct) ? $deliveryProduct : [];
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Product\DeliveryProductInterface[] $deliveryProduct
     * @return void
     */
    public function setDeliveryProduct(array $deliveryProduct): void
    {
        $this->setData(self::DELIVERY_PRODUCT, $deliveryProduct);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Product\DeliveryProductInterface[]|null
     */
    public function getDeliveryRelatedProducts(): ?array
    {
        $deliveryRelatedProducts = $this->getData(self::DELIVERY_RELATED_PRODUCTS);

        return is_array($deliveryRelatedProducts) ? $deliveryRelatedProducts : null;
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Product\DeliveryProductInterface[] $deliveryRelatedProducts
     * @return void
     */
    public function setDeliveryRelatedProducts(array $deliveryRelatedProducts): void
    {
        $this->setData(self::DELIVERY_RELATED_PRODUCTS, $deliveryRelatedProducts);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Product\AdditionalImageInterface[]
     */
    public function getAdditionalProductImages(): array
    {
        $additionalProductImages = $this->getData(self::ADDITIONAL_PRODUCT_IMAGES);

        return is_array($additionalProductImages) ? $additionalProductImages : [];
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Product\AdditionalImageInterface[] $additionalProductImages
     * @return void
     */
    public function setAdditionalProductImages(array $additionalProductImages): void
    {
        $this->setData(self::ADDITIONAL_PRODUCT_IMAGES, $additionalProductImages);
    }
}
