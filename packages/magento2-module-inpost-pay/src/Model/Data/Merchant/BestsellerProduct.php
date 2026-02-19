<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\BestsellerQuantityInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\BestsellerQuantityInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\ProductAvailabilityInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Api\ExtensibleDataInterface;

class BestsellerProduct extends DataObject implements BestsellerProductInterface, ExtensibleDataInterface
{
    /**
     * @param PriceInterfaceFactory $priceFactory
     * @param BestsellerQuantityInterfaceFactory $bestsellerQuantityFactory
     * @param array $data
     */
    public function __construct(
        private readonly PriceInterfaceFactory $priceFactory,
        private readonly BestsellerQuantityInterfaceFactory $bestsellerQuantityFactory,
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
     * @return string|null
     */
    public function getStatus(): ?string
    {
        $status = $this->getData(self::STATUS);

        return is_scalar($status) ? (string)$status : null;
    }

    /**
     * @param string|null $status
     * @return void
     */
    public function setStatus(?string $status): void
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @return string|null
     */
    public function getEan(): ?string
    {
        $ean = $this->getData(self::EAN);

        return is_scalar($ean) ? (string)$ean : null;
    }

    /**
     * @param string|null $ean
     * @return void
     */
    public function setEan(?string $ean): void
    {
        $this->setData(self::EAN, $ean);
    }

    /**
     * @return string|null
     */
    public function getQrCode(): ?string
    {
        $qrCode = $this->getData(self::QR_CODE);

        return is_scalar($qrCode) ? (string)$qrCode : null;
    }

    /**
     * @param string|null $qrCode
     * @return void
     */
    public function setQrCode(?string $qrCode): void
    {
        $this->setData(self::QR_CODE, $qrCode);
    }

    /**
     * @return string|null
     */
    public function getDeepLink(): ?string
    {
        $deepLink = $this->getData(self::DEEP_LINK);

        return is_scalar($deepLink) ? (string)$deepLink : null;
    }

    /**
     * @param string|null $deepLink
     * @return void
     */
    public function setDeepLink(?string $deepLink): void
    {
        $this->setData(self::DEEP_LINK, $deepLink);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\ProductAvailabilityInterface|null
     */
    public function getProductAvailability(): ?ProductAvailabilityInterface
    {
        $productAvailability = $this->getData(self::PRODUCT_AVAILABILITY);

        if ($productAvailability instanceof ProductAvailabilityInterface) {
            return $productAvailability;
        }

        return null;
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\ProductAvailabilityInterface|null $availability
     * @return void
     */
    public function setProductAvailability(?ProductAvailabilityInterface $availability): void
    {
        $this->setData(self::PRODUCT_AVAILABILITY, $availability);
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
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Product\AdditionalImageInterface[]
     */
    public function getAdditionalProductImages(): array
    {
        $additionalImages = $this->getData(self::ADDITIONAL_PRODUCT_IMAGES);

        return is_array($additionalImages) ? $additionalImages : [];
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Product\AdditionalImageInterface[] $additionalImages
     * @return void
     */
    public function setAdditionalProductImages(array $additionalImages): void
    {
        $this->setData(self::ADDITIONAL_PRODUCT_IMAGES, $additionalImages);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getPrice(): PriceInterface
    {
        $price = $this->getData(self::PRICE);

        if ($price instanceof PriceInterface) {
            return $price;
        }

        return $this->priceFactory->create();
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $price
     * @return void
     */
    public function setPrice(PriceInterface $price): void
    {
        $this->setData(self::PRICE, $price);
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        $currency = $this->getData(self::CURRENCY);

        return is_scalar($currency) ? (string)$currency : self::DEFAULT_CURRENCY;
    }

    /**
     * @param string $currency
     * @return void
     */
    public function setCurrency(string $currency): void
    {
        $this->setData(self::CURRENCY, $currency);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\BestsellerQuantityInterface
     */
    public function getQuantity(): BestsellerQuantityInterface
    {
        $quantity = $this->getData(self::QUANTITY);

        if ($quantity instanceof BestsellerQuantityInterface) {
            return $quantity;
        }

        return $this->bestsellerQuantityFactory->create();
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\BestsellerQuantityInterface $quantity
     * @return void
     */
    public function setQuantity(BestsellerQuantityInterface $quantity): void
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
}
