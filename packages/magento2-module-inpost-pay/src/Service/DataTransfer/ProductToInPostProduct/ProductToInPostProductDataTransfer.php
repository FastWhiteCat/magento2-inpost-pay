<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct;

use InPost\InPostPay\Api\Data\Merchant\Basket\Product\ProductAttributeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Product\ProductAttributeInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use InPost\InPostPay\Model\Data\Merchant\Basket\Product\Quantity;
use InPost\InPostPay\Model\Utils\StringUtils;
use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySales\Model\IsProductSalableCondition\ManageStockCondition;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Catalog\Api\Data\ProductInterface as MagentoProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\App\Emulation;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductToInPostProductDataTransfer
{
    public const INT_QTY = 'INTEGER';
    public const FLOAT_QTY = 'DECIMAL';

    public const UNMANAGED_STOCK_QUANTITY = 9999;

    private ?MagentoProductInterface $product = null;

    private WriteInterface $mediaDirectory;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly ProductAttributeInterfaceFactory $productAttributeFactory,
        private readonly StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly GetStockItemConfigurationInterface $getStockItemConfiguration,
        private readonly GetProductSalableQtyInterface $getProductSalableQty,
        private readonly ManageStockCondition $manageStockCondition,
        private readonly StringUtils $stringUtils,
        private readonly Escaper $escaper,
        private readonly ImageHelper $imageHelper,
        private readonly GeneralConfigProvider $generalConfigProvider,
        private readonly Emulation $emulation,
        Filesystem $filesystem
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    public function transfer(
        Product $product,
        ProductInterface $inPostProduct,
        int $websiteId,
        ?float $quantity = null,
        array $selectedOptions = [],
        array $quoteItemsQuantity = []
    ): void {
        if ($product->getTypeId() === Type::TYPE_BUNDLE) {
            if ($quantity === null) {
                $quantity = 1.0;
            }
            $bundleQuantity = $this->getBundleQuantity($product, $quantity, $websiteId, $quoteItemsQuantity);
            $canCastQtyToInt = $this->canCastToInteger($quantity);
            $maxQuantity = $bundleQuantity['maxQuantity'];
            $stockQuantity = $bundleQuantity['stockQuantity'];
        } else {
            $stockId = (int)$this->stockByWebsiteIdResolver->execute($websiteId)->getStockId();
            $stockItemConfiguration = $this->getStockItemConfiguration->execute($product->getSku(), $stockId);
            if ($quantity === null) {
                $quantity = $stockItemConfiguration->getMinSaleQty();
            }
            $canCastQtyToInt = $this->canCastToInteger($quantity);

            $stockQuantity = $this->getSimpleProductStockQuantity($stockId, $product, $quantity, $canCastQtyToInt);
            $maxQuantity = min([$stockItemConfiguration->getMaxSaleQty(), $stockQuantity]);
            if ($quoteItemsQuantity) {
                $maxQuantity -= ($quoteItemsQuantity[$product->getId()] - $quantity);
                $stockQuantity -= ($quoteItemsQuantity[$product->getId()] - $quantity);
            }
            $maxQuantity = $canCastQtyToInt ? (int)$maxQuantity : (float)$maxQuantity;
        }

        $description = $this->getDescription($product);
        $regularPrice = $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount();
        $regularPriceExclTax = DecimalCalculator::round((float)$regularPrice->getBaseAmount());
        $regularPriceInclTax = DecimalCalculator::round((float)$regularPrice->getValue());

        $productId = $this->extractProductId($product);

        $inPostProduct->setProductId($productId);
        $inPostProduct->setProductCategory(
            $product->getCategoryIds() ? (string)max($product->getCategoryIds()) : ''
        );
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
        $unit = Quantity::DEFAULT_UNIT;
        $quantityObj->setQuantityUnit(__($unit)->render());
        $quantityObj->setAvailableQuantity($stockQuantity);
        $quantityObj->setMaxQuantity($maxQuantity);
        $inPostProduct->setQuantity($quantityObj);
        $inPostProduct->setProductAttributes($this->getProductAttributes($product, $selectedOptions));
    }

    private function getProductImageUrl(Product $originalProduct): string
    {
        $storeId = (int)$originalProduct->getStoreId();
        $originalProductSku = (string)$originalProduct->getSku();
        $product = $this->productRepository->get($originalProductSku, false, $storeId);
        $this->emulation->startEnvironmentEmulation($storeId, 'frontend', true);

        $imageRole = $this->generalConfigProvider->getImageRole();
        $productImageRole = $product->getData($imageRole);
        $image = is_scalar($productImageRole) ? (string)$productImageRole : '';

        if (empty($image) && (int)$product->getId() !== (int)$originalProduct->getId()) {
            //If this product comes from quoteItem than SKU belongs to simple but ID remains to parent,
            //In case of no image for simple product, image will be loaded from parent configurable product
            $product = $this->productRepository->getById((int)$originalProduct->getId(), false, $storeId);
            $productImageRole = $product->getData($imageRole);
            $image = is_scalar($productImageRole) ? (string)$productImageRole : '';
        }

        $imgPath = $product->getMediaConfig()->getMediaPath($image);

        if (!$this->mediaDirectory->isExist($imgPath) || !$this->mediaDirectory->isFile($imgPath)) {
            return $this->imageHelper->getDefaultPlaceholderUrl('image');
        }

        $imgUrl = $product->getMediaConfig()->getMediaUrl($image);

        $this->emulation->stopEnvironmentEmulation();

        return $imgUrl;
    }

    private function getProductAttributes(Product $product, array $selectedOptions = []): array
    {
        $productAttributesData = [];

        foreach ($selectedOptions as $selectedOption) {
            /** @var ProductAttributeInterface $inPostProductAttribute */
            $inPostProductAttribute = $this->productAttributeFactory->create();
            $inPostProductAttribute->setAttributeName($this->escaper->escapeUrl($selectedOption['label']));
            $inPostProductAttribute->setAttributeValue($this->escaper->escapeUrl($selectedOption['value']));
            $productAttributesData[] = $inPostProductAttribute;
        }

        $product = $this->getProduct($product);
        if ($product instanceof Product) {
            $attributes = $product->getAttributes();
            foreach ($attributes as $attribute) {
                if ($attribute->getIsVisibleOnFront()) {
                    $value = $attribute->getFrontend()->getValue($product);
                    if (is_string($value)) {
                        $cleanValue = trim($this->stringUtils->cleanUpString($value));
                    } else {
                        continue;
                    }

                    if (strlen($cleanValue)) {
                        /** @var ProductAttributeInterface $inPostProductAttribute */
                        $inPostProductAttribute = $this->productAttributeFactory->create();
                        $storeLabel = $attribute->getStoreLabel((int)$product->getStoreId());
                        $inPostProductAttribute->setAttributeName($this->escaper->escapeUrl($storeLabel));
                        $inPostProductAttribute->setAttributeValue($cleanValue);
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

    private function getDescription(Product $product): string
    {
        $product = $this->getProduct($product);
        $description = '';

        if ($product instanceof Product) {
            $description = $product->getData('short_description') ?? $product->getData('description');
            $description = is_scalar($description) ? (string)$description : '';
            $description = $this->stringUtils->cleanUpString($description);
        }

        return $description;
    }

    private function getProduct(Product $product): ?MagentoProductInterface
    {
        if ($this->product && $this->product->getId() === $product->getId()) {
            return $this->product;
        }

        try {
            $this->product = $this->productRepository->getById(
                (int)$product->getId(),
                false,
                (int)$product->getStoreId()
            );
        } catch (NoSuchEntityException $e) {
            $this->product = null;
        }

        return $this->product;
    }

    private function getBundleQuantity(
        Product $product,
        float $quantity,
        int $websiteId,
        array $quoteItemsQuantity = []
    ): array {
        $maxBundleQuantity = null;
        $bundleStockQuantity = null;
        /** @var AbstractItem[] $children */
        $children = $product->getData('children');
        $stockId = (int)$this->stockByWebsiteIdResolver->execute($websiteId)->getStockId();

        foreach ($children as $child) {
            $stockItemConfiguration = $this->getStockItemConfiguration->execute($child->getSku(), $stockId);
            $childQuantity = $child->getQty();
            $canCastQtyToInt = $this->canCastToInteger($childQuantity);
            $stockQuantity = $this->getSimpleProductStockQuantity($stockId, $child, $childQuantity, $canCastQtyToInt);

            $maxQuantity = min([$stockItemConfiguration->getMaxSaleQty(), $stockQuantity]);
            if ($quoteItemsQuantity) {
                $maxQuantity -= ($quoteItemsQuantity[$child->getProduct()->getId()] - ($childQuantity * $quantity));
                $stockQuantity -= ($quoteItemsQuantity[$child->getProduct()->getId()] - ($childQuantity * $quantity));
            }

            $maxQuantity = (int)($maxQuantity / $childQuantity);
            $stockQuantity = (int)($stockQuantity / $childQuantity);

            if ($bundleStockQuantity === null) {
                $bundleStockQuantity = $stockQuantity;
            }
            $bundleStockQuantity = min([$bundleStockQuantity, $stockQuantity]);
            if ($maxBundleQuantity === null) {
                $maxBundleQuantity = $maxQuantity;
            }
            $maxBundleQuantity = min([$maxBundleQuantity, $maxQuantity]);
        }

        return [
            'maxQuantity' =>  (float)$maxBundleQuantity,
            'stockQuantity' => (float)$bundleStockQuantity
        ];
    }

    private function extractProductId(Product $product): string
    {
        if ($product->getData('simple_product_id') && is_scalar($product->getData('simple_product_id'))) {
            return (string)$product->getData('simple_product_id');
        }

        return (string)$product->getId();
    }

    private function getSimpleProductStockQuantity(
        int $stockId,
        AbstractItem | Product $product,
        float $quantity,
        bool $canCastQtyToInt
    ): int|float {
        try {
            $stockQuantity = $this->getProductSalableQty->execute($product->getSku(), $stockId);
        } catch (InputException | LocalizedException $e) {
            $stockQuantity = $quantity;
        }

        $manageStock = $this->manageStockCondition->execute($product->getSku(), $stockId);
        if ($stockQuantity <= 0 && $manageStock) {
            $stockQuantity = self::UNMANAGED_STOCK_QUANTITY;
        }

        return $canCastQtyToInt ? (int)$stockQuantity : (float)$stockQuantity;
    }
}
