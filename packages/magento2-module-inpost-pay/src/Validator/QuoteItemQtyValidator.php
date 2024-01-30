<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class QuoteItemQtyValidator
{
    public function __construct(
        private readonly StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly GetStockItemConfigurationInterface $getStockItemConfiguration,
        private readonly GetProductSalableQtyInterface $getProductSalableQty,
    ) {
    }

    public function validate(
        Quote $quote,
        int $productId,
        float $requestedQuantity,
        bool $isQuoteItemId,
        array $quoteItemsQuantity
    ): bool {
        $websiteId = (int)$quote->getStore()->getWebsiteId();

        if ($isQuoteItemId) {
            $quoteItem = $quote->getItemById($productId);
            if ($quoteItem) {
                $maxQuantity = $this->getBundleQuantity(
                    $quoteItem->getChildren(),
                    $quoteItem->getQty(),
                    $websiteId,
                    $quoteItemsQuantity
                );
            }
        } else {
            $product = $this->productRepository->getById($productId, false, $quote->getStoreId());

            if (!$product instanceof Product) {
                return false;
            }
            $quoteItem = $quote->getItemByProduct($product);

            $stockId = (int)$this->stockByWebsiteIdResolver->execute($websiteId)->getStockId();
            $stockItemConfiguration = $this->getStockItemConfiguration->execute($product->getSku(), $stockId);
            $stockQuantity = $this->getSimpleProductStockQuantity($stockId, $product, $requestedQuantity);
            $maxQuantity = min([$stockItemConfiguration->getMaxSaleQty(), $stockQuantity]);
            if ($quoteItemsQuantity) {
                $maxQuantity -= ($quoteItemsQuantity[$product->getId()] - $quoteItem->getQty());
            }
            $maxQuantity = (float)$maxQuantity;
        }

        return $requestedQuantity <= $maxQuantity;
    }

    private function getBundleQuantity(
        array $children,
        float $quantity,
        int $websiteId,
        array $quoteItemsQuantity = []
    ): float {
        $maxBundleQuantity = null;
        $stockId = (int)$this->stockByWebsiteIdResolver->execute($websiteId)->getStockId();

        foreach ($children as $child) {
            $stockItemConfiguration = $this->getStockItemConfiguration->execute($child->getSku(), $stockId);
            $childQuantity = $child->getQty();
            $stockQuantity = $this->getSimpleProductStockQuantity($stockId, $child, $childQuantity);

            $maxQuantity = min([$stockItemConfiguration->getMaxSaleQty(), $stockQuantity]);
            if ($quoteItemsQuantity) {
                $maxQuantity -= ($quoteItemsQuantity[$child->getProduct()->getId()] - ($childQuantity * $quantity));
            }

            $maxQuantity = (int)($maxQuantity / $childQuantity);
            if ($maxBundleQuantity === null) {
                $maxBundleQuantity = $maxQuantity;
            } else {
                $maxBundleQuantity = min([$maxBundleQuantity, $maxQuantity]);
            }
        }

        return (float)$maxBundleQuantity;
    }

    private function getSimpleProductStockQuantity(
        int $stockId,
        AbstractItem | Product $product,
        float $quantity,
    ): float {
        try {
            $stockQuantity = $this->getProductSalableQty->execute($product->getSku(), $stockId);
        } catch (InputException | LocalizedException $e) {
            $stockQuantity = $quantity;
        }

        return (float)$stockQuantity;
    }
}
