<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct;

use InPost\InPostPay\Api\Data\Merchant\Basket\Product\ProductAttributeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Product\DeliveryProductInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\Product\ProductAttributeInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use InPost\InPostPay\Enum\InPostDeliveryType;
use InPost\InPostPay\Model\Data\Merchant\Basket\Product\Quantity;
use InPost\InPostPay\Model\Utils\StringUtils;
use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Provider\RestrictedProductIdsProvider;
use Magento\Catalog\Model\Product\Type;
use InPost\InPostPay\Enum\InPostProductType;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySales\Model\IsProductSalableCondition\ManageStockCondition;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Pricing\Price\RegularPrice;
use InPost\InPostPay\Service\Product\ProductBackorderService;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\App\Emulation;
use Magento\Swatches\Helper\Data as SwatchesHelper;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductToInPostProductDataTransfer
{
    public const CONFIGURABLE_PARENT_PRODUCT = 'configurable_parent_product';
    public const CONFIGURABLE_CHILD_PRODUCT = 'configurable_child_product';
    public const BUNDLE_CHILD_PRODUCTS = 'bundle_child_products';
    public const INT_QTY = 'INTEGER';
    public const FLOAT_QTY = 'DECIMAL';

    public const UNMANAGED_STOCK_QUANTITY = 9999;

    public const ALL_DELIVERY_TYPES = [
        RestrictionsRuleInterface::APPLIES_TO_COURIER => InPostDeliveryType::COURIER,
        RestrictionsRuleInterface::APPLIES_TO_APM => InPostDeliveryType::APM,
        RestrictionsRuleInterface::APPLIES_TO_DIGITAL => InPostDeliveryType::DIGITAL
    ];

    private WriteInterface $mediaDirectory;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly DeliveryProductInterfaceFactory $deliveryProductFactory,
        private readonly ProductAttributeInterfaceFactory $productAttributeFactory,
        private readonly RestrictedProductIdsProvider $restrictedProductIdsProvider,
        private readonly StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        private readonly GetStockItemConfigurationInterface $getStockItemConfiguration,
        private readonly GetProductSalableQtyInterface $getProductSalableQty,
        private readonly ManageStockCondition $manageStockCondition,
        private readonly StringUtils $stringUtils,
        private readonly Escaper $escaper,
        private readonly ImageHelper $imageHelper,
        private readonly AdditionalProductImagesDataTransfer $additionalProductImagesDataTransfer,
        private readonly GeneralConfigProvider $generalConfigProvider,
        private readonly Emulation $emulation,
        private readonly LoggerInterface $logger,
        private readonly ProductBackorderService $productBackorderService,
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
        array $quoteItemsQuantity = [],
        bool $isRelatedProduct = false
    ): void {
        if ($product->getTypeId() === Type::TYPE_BUNDLE) {
            $quantity = $quantity ?? 1.0;
            $bundleQuantity = $this->getBundleQuantity($product, $quantity, $websiteId, $quoteItemsQuantity);
            $canCastQtyToInt = $this->canCastToInteger($quantity);
            $maxQuantity = $bundleQuantity['maxQuantity'];
            $stockQuantity = $bundleQuantity['stockQuantity'];
        } else {
            $stockId = (int)$this->stockByWebsiteIdResolver->execute($websiteId)->getStockId();
            $stockItemConfiguration = $this->getStockItemConfiguration->execute($product->getSku(), $stockId);
            $quantity = $quantity ?? $stockItemConfiguration->getMinSaleQty();
            $canCastQtyToInt = $this->canCastToInteger($quantity);
            $stockQuantity = $this->getSimpleProductStockQuantity($stockId, $product, $quantity, $canCastQtyToInt);
            $maxQuantity = min([$stockItemConfiguration->getMaxSaleQty(), $stockQuantity]);

            if ($quoteItemsQuantity) {
                $maxQuantity -= ($quoteItemsQuantity[$product->getId()] - $quantity);
                $stockQuantity -= ($quoteItemsQuantity[$product->getId()] - $quantity);
            }
            $maxQuantity = $canCastQtyToInt ? (int)$maxQuantity : (float)$maxQuantity;
        }

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
        $inPostProduct->setProductDescription($this->prepareProductDescription($product));
        $inPostProduct->setProductLink($this->prepareProductUrl($product));
        $inPostProduct->setProductImage($this->prepareProductImageUrl($product));
        $inPostProduct->setProductType(InPostProductType::PRODUCT->value);

        if ($product->isVirtual()) {
            $inPostProduct->setProductType(InPostProductType::DIGITAL->value);
        }

        $basePrice = $inPostProduct->getBasePrice();
        $basePrice->setNet($regularPriceExclTax);
        $basePrice->setGross($regularPriceInclTax);
        $basePrice->setVat(DecimalCalculator::sub($regularPriceInclTax, $regularPriceExclTax));
        $inPostProduct->setBasePrice($basePrice);
        $quantityObj = $inPostProduct->getQuantity();
        if ($canCastQtyToInt) {
            $quantityObj->setQuantity((int)$quantity);
            $quantityObj->setQuantityType(self::INT_QTY);
        } else {
            $quantityObj->setQuantity($quantity);
            $quantityObj->setQuantityType(self::FLOAT_QTY);
        }
        $unit = Quantity::DEFAULT_UNIT;
        $quantityObj->setQuantityUnit(__($unit)->render());
        $quantityObj->setAvailableQuantity($stockQuantity);
        $quantityObj->setMaxQuantity($maxQuantity);
        $inPostProduct->setQuantity($quantityObj);
        $inPostProduct->setProductAttributes($this->getProductAttributes($product, $selectedOptions));
        $deliveryProductArray = $this->getDeliveryProduct($product, $websiteId);

        if ($isRelatedProduct) {
            $inPostProduct->setDeliveryRelatedProducts($deliveryProductArray);
        } else {
            $inPostProduct->setDeliveryProduct($deliveryProductArray);
        }

        $this->additionalProductImagesDataTransfer->transfer($product, $inPostProduct);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    private function prepareProductImageUrl(Product $product): string
    {
        $storeId = (int)$product->getStoreId();
        $this->emulation->startEnvironmentEmulation($storeId, 'frontend', true);

        $imageRole = $this->generalConfigProvider->getImageRole($product->getStoreId());
        $productImageRole = $product->getData($imageRole);
        $image = is_scalar($productImageRole) ? (string)$productImageRole : '';

        if ((empty($image) || $image === SwatchesHelper::EMPTY_IMAGE_VALUE)
            && $product->hasData(self::CONFIGURABLE_PARENT_PRODUCT)
            && $product->getData(self::CONFIGURABLE_PARENT_PRODUCT) instanceof Product
        ) {
            /** @var Product $product */
            $product = $product->getData(self::CONFIGURABLE_PARENT_PRODUCT);
            $productImageRole = $product->getData($imageRole);
            $image = is_scalar($productImageRole) ? (string)$productImageRole : '';
        }

        // @phpstan-ignore-next-line
        $imgPath = $product->getMediaConfig()->getMediaPath($image);

        if (!$this->mediaDirectory->isExist($imgPath) || !$this->mediaDirectory->isFile($imgPath)) {
            $this->logger->debug(sprintf('Image (%s) not found for product ID: %s', $image, (int)$product->getId()));

            $placeholderImageUrl = $this->imageHelper->getDefaultPlaceholderUrl('image');
            $this->emulation->stopEnvironmentEmulation();

            return $placeholderImageUrl;
        }

        // @phpstan-ignore-next-line
        $imgUrl = $product->getMediaConfig()->getMediaUrl($image);

        $this->emulation->stopEnvironmentEmulation();

        return $imgUrl;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    private function prepareProductUrl(Product $product): string
    {
        if ($product->hasData(self::CONFIGURABLE_PARENT_PRODUCT)
            && $product->getData(self::CONFIGURABLE_PARENT_PRODUCT) instanceof Product
        ) {
            /** @var Product $product */
            $product = $product->getData(self::CONFIGURABLE_PARENT_PRODUCT);
        }

        return $product->getProductUrl();
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

        if ($product->hasData(self::CONFIGURABLE_PARENT_PRODUCT)
            && $product->getData(self::CONFIGURABLE_PARENT_PRODUCT) instanceof Product
        ) {
            $product = $product->getData(self::CONFIGURABLE_PARENT_PRODUCT);
        }

        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if (!$attribute->getIsVisibleOnFront()) {
                continue;
            }

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

        return $productAttributesData;
    }

    private function canCastToInteger(float $value): bool
    {
        return number_format(round($value, 2), 2, '.', '') === number_format((int)$value, 2, '.', '');
    }

    private function prepareProductDescription(Product $product): string
    {
        $description = $product->getData('short_description') ?? $product->getData('description');
        $description = is_scalar($description) ? (string)$description : '';

        if (empty($description)
            && $product->hasData(self::CONFIGURABLE_PARENT_PRODUCT)
            && $product->getData(self::CONFIGURABLE_PARENT_PRODUCT) instanceof Product
        ) {
            $product = $product->getData(self::CONFIGURABLE_PARENT_PRODUCT);
            $description = $product->getData('short_description') ?? $product->getData('description');
            $description = is_scalar($description) ? (string)$description : '';
        }

        return $this->stringUtils->cleanUpString($description);
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
        $children = $product->getData(self::BUNDLE_CHILD_PRODUCTS);
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
        if ($product->getData(self::CONFIGURABLE_CHILD_PRODUCT)
            && is_scalar($product->getData(self::CONFIGURABLE_CHILD_PRODUCT))
        ) {
            return (string)$product->getData(self::CONFIGURABLE_CHILD_PRODUCT);
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

        $unmanagedStock = $this->manageStockCondition->execute($product->getSku(), $stockId);
        $isBackOrdered = $this->productBackorderService->isProductBackOrdered($product, $stockId);

        if ($unmanagedStock || $isBackOrdered) {
            $stockQuantity = $this->productBackorderService->getBackOrderMaxSalesQty($product, $stockId);
        }

        return $canCastQtyToInt ? (int)$stockQuantity : (float)$stockQuantity;
    }

    private function getDeliveryProduct(Product $product, int $websiteId): array
    {
        $productId = (int)$product->getId();
        $simpleProductId = (int)$this->extractProductId($product);

        $isRestricted = $this->isProductRestricted($productId, $websiteId);
        $isRestricted = $isRestricted || $this->isProductRestricted($simpleProductId, $websiteId);

        $deliveryProductArr = [];
        foreach (self::ALL_DELIVERY_TYPES as $key => $enum) {
            $available = $this->isDeliveryForProductAvailable(
                $product,
                $websiteId,
                $key,
                $isRestricted,
                $simpleProductId
            );
            $deliveryProduct = $this->deliveryProductFactory->create();
            $deliveryProduct->setDeliveryType($enum->value);
            $deliveryProduct->setIfDeliveryAvailable($available);
            $deliveryProductArr[] = $deliveryProduct;
        }

        return $deliveryProductArr;
    }

    private function isDeliveryForProductAvailable(
        Product $product,
        int $websiteId,
        int $deliveryType,
        bool $isRestricted,
        int $simpleProductId
    ): bool {
        $productId = (int)$product->getId();

        if ($product->isVirtual()) {
            if ($deliveryType !== RestrictionsRuleInterface::APPLIES_TO_DIGITAL) {
                $available = false;
            } else {
                $available = !$isRestricted;
            }
        } else {
            if ($isRestricted) {
                $available = false;
            } else {
                $available = !(
                    $this->isProductRestricted($productId, $websiteId, $deliveryType)
                    || $this->isProductRestricted($simpleProductId, $websiteId, $deliveryType)
                );
            }

            $available = ($deliveryType === RestrictionsRuleInterface::APPLIES_TO_DIGITAL) ? false : $available;
        }

        return $available;
    }

    private function isProductRestricted(int $productId, int $websiteId, int $appliesTo = 0): bool
    {
        return in_array(
            $productId,
            $this->restrictedProductIdsProvider->getList($websiteId, $appliesTo)
        );
    }
}
