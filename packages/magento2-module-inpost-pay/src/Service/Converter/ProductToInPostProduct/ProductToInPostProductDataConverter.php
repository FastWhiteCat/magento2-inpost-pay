<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter\ProductToInPostProduct;

use InPost\InPostPay\Api\ApiConnector\IziApi\Product\ProductFieldInterface as InPostProduct;
use InPost\InPostPay\Api\ApiConnector\IziApi\Basket\BasketFieldInterface as Basket;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Catalog\Model\Product;

class ProductToInPostProductDataConverter
{
    public const INT_QTY = 'INTEGER';
    public const FLOAT_QTY = 'DECIMAL';
    public const QTY_UNIT = 'pcs';

    public function __construct(
        private readonly StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly GetStockItemConfigurationInterface $getStockItemConfiguration,
        private readonly GetProductSalableQtyInterface $getProductSalableQty,
        private readonly Escaper $escaper,
        private readonly ImageHelper $imageHelper
    ) {
    }

    public function convert(Product $product, int $websiteId, ?float $quantity = null): array
    {
        $stockId = (int)$this->stockByWebsiteIdResolver->execute($websiteId)->getStockId();
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($product->getSku(), $stockId);
        if ($quantity === null) {
            $quantity = $stockItemConfiguration->getMinSaleQty();
        }
        $description = ($product->getData('short_description') ?? $product->getData('description'));
        $canCastQtyToInt = $this->canCastToInteger($quantity);
        $stockQuantity = $this->getProductSalableQty->execute($product->getSku(), $stockId);
        $stockQuantity = $canCastQtyToInt ? (int)$stockQuantity : (float)$stockQuantity;
        $maxQuantity = min([$stockItemConfiguration->getMaxSaleQty(), $stockQuantity]);
        $maxQuantity = $canCastQtyToInt ? (int)$maxQuantity : (float)$maxQuantity;
        $description = (is_scalar($description)) ? (string)$description : '';
        $regularPrice = $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount();
        $regularPriceExclTax = DecimalCalculator::round((float)$regularPrice->getBaseAmount());
        $regularPriceInclTax = DecimalCalculator::round((float)$regularPrice->getValue());

        return [
            InPostProduct::PRODUCT_ID => (int)$product->getId(),
            InPostProduct::PRODUCT_CATEGORY => (int)max($product->getCategoryIds()),
            InPostProduct::EAN => (string)$product->getSku(),
            InPostProduct::PRODUCT_NAME => (string)$product->getName(),
            InPostProduct::PRODUCT_DESCRIPTION => $description,
            InPostProduct::PRODUCT_LINK => $product->getProductUrl(),
            InPostProduct::PRODUCT_IMAGE => $this->getProductImageUrl($product),
            InPostProduct::BASE_PRICE => [
                Basket::NET => $regularPriceExclTax,
                Basket::GROSS => $regularPriceInclTax,
                Basket::VAT =>  DecimalCalculator::sub($regularPriceInclTax, $regularPriceExclTax)
            ],
            InPostProduct::QUANTITY => [
                InPostProduct::QUANTITY => $canCastQtyToInt ? (int)$quantity : $quantity,
                InPostProduct::QUANTITY_TYPE => $canCastQtyToInt ? self::INT_QTY : self::FLOAT_QTY,
                InPostProduct::QUANTITY_UNIT => self::QTY_UNIT,
                InPostProduct::AVAILABLE_QUANTITY => $stockQuantity,
                InPostProduct::MAX_QUANTITY => $maxQuantity,
            ],
            InPostProduct::PRODUCT_ATTRIBUTES => $this->getProductAttributes($product)
        ];
    }

    private function getProductImageUrl(Product $product): string
    {
        $imageUrl = '';
        $smallImageAttrValue = $product->getData('small_image');
        if (is_scalar($smallImageAttrValue)) {
            $imageUrl = $this->imageHelper->init($product, 'product_page_image_small')
                ->setImageFile((string)$smallImageAttrValue)
                ->getUrl();
        }

        return $imageUrl;
    }

    private function getProductAttributes(Product $product): array
    {
        try {
            $product = $this->productRepository->getById((int)$product->getId(), false, (int)$product->getStoreId());
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        $productAttributesData = [];
        if ($product instanceof Product) {
            $attributes = $product->getAttributes();
            foreach ($attributes as $attribute) {
                if ($attribute->getIsVisibleOnFront()) {
                    $value = $attribute->getFrontend()->getValue($product);
                    if (is_string($value) && strlen(trim($value))) {
                        $productAttributesData[] = [
                            InPostProduct::ATTRIBUTE_NAME => $this->escaper->escapeUrl($attribute->getStoreLabel()),
                            InPostProduct::ATTRIBUTE_VALUE => $this->escaper->escapeUrl($value)
                        ];
                    }
                }
            }
        }

        return $productAttributesData;
    }

    private function canCastToInteger(float $value): bool
    {
        return number_format(round($value, 2), 2, '.', '') === number_format((int)$value, 2, '.', '');
    }
}
