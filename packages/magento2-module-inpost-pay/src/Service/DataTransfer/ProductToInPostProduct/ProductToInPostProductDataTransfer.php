<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct;

use InPost\InPostPay\Api\Data\Merchant\Basket\Product\ProductAttributeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Product\ProductAttributeInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Catalog\Model\Product;

class ProductToInPostProductDataTransfer
{
    public const INT_QTY = 'INTEGER';
    public const FLOAT_QTY = 'DECIMAL';
    public const QTY_UNIT = 'pcs';

    public function __construct(
        private readonly ProductAttributeInterfaceFactory $productAttributeFactory,
        private readonly StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly GetStockItemConfigurationInterface $getStockItemConfiguration,
        private readonly GetProductSalableQtyInterface $getProductSalableQty,
        private readonly Escaper $escaper,
        private readonly ImageHelper $imageHelper
    ) {
    }

    public function transfer(
        Product $product,
        ProductInterface $inPostProduct,
        int $websiteId,
        ?float $quantity = null
    ): void {
        $stockId = (int)$this->stockByWebsiteIdResolver->execute($websiteId)->getStockId();
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($product->getSku(), $stockId);
        if ($quantity === null) {
            $quantity = $stockItemConfiguration->getMinSaleQty();
        }
        $description = ($product->getData('short_description') ?? $product->getData('description'));
        $canCastQtyToInt = $this->canCastToInteger($quantity);
        try {
            $stockQuantity = $this->getProductSalableQty->execute($product->getSku(), $stockId);
        } catch (InputException | LocalizedException $e) {
            $stockQuantity = $quantity;
        }
        $stockQuantity = $canCastQtyToInt ? (int)$stockQuantity : (float)$stockQuantity;
        $maxQuantity = min([$stockItemConfiguration->getMaxSaleQty(), $stockQuantity]);
        $maxQuantity = $canCastQtyToInt ? (int)$maxQuantity : (float)$maxQuantity;
        $description = (is_scalar($description)) ? (string)$description : '';
        $regularPrice = $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount();
        $regularPriceExclTax = DecimalCalculator::round((float)$regularPrice->getBaseAmount());
        $regularPriceInclTax = DecimalCalculator::round((float)$regularPrice->getValue());

        $inPostProduct->setProductId((string)$product->getId());
        $inPostProduct->setProductCategory((string)max($product->getCategoryIds()));
        $inPostProduct->setEan((string)$product->getSku());
        $inPostProduct->setProductName((string)$product->getName());
        $inPostProduct->setProductDescription($description);
        $inPostProduct->setProductLink($product->getProductUrl());
        $inPostProduct->setProductImage($this->getProductImageUrl($product));
        $basePrice = $inPostProduct->getBasePrice();
        $basePrice->setNet($regularPriceExclTax);
        $basePrice->setGross($regularPriceInclTax);
        $basePrice->setVat(DecimalCalculator::sub($regularPriceInclTax, $regularPriceExclTax));
        $inPostProduct->setBasePrice($basePrice);
        $quantityObj = $inPostProduct->getQuantity();
        $quantityObj->setQuantity($canCastQtyToInt ? (int)$quantity : $quantity);
        $quantityObj->setQuantityType($canCastQtyToInt ? self::INT_QTY : self::FLOAT_QTY);
        $quantityObj->setQuantityUnit(self::QTY_UNIT);
        $quantityObj->setAvailableQuantity($stockQuantity);
        $quantityObj->setMaxQuantity($maxQuantity);
        $inPostProduct->setQuantity($quantityObj);
        $inPostProduct->setProductAttributes($this->getProductAttributes($product));
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
                        /** @var ProductAttributeInterface $inPostProductAttribute */
                        $inPostProductAttribute = $this->productAttributeFactory->create();
                        $storeLabel = $attribute->getStoreLabel((int)$product->getStoreId());
                        $inPostProductAttribute->setAttributeName($this->escaper->escapeUrl($storeLabel));
                        $inPostProductAttribute->setAttributeValue($this->escaper->escapeUrl($value));
                        $productAttributesData[] = $inPostProductAttribute;
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
