<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator;

use InPost\InPostPay\Exception\QuoteItemOutOfStockException;
use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\InventorySales\Model\IsProductSalableCondition\ManageStockCondition;
use InPost\InPostPay\Service\Product\ProductBackorderService;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteItemQtyValidator
{
    public function __construct(
        private readonly StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly GetStockItemConfigurationInterface $getStockItemConfiguration,
        private readonly GetProductSalableQtyInterface $getProductSalableQty,
        private readonly ManageStockCondition $manageStockCondition,
        private readonly ProductBackorderService $productBackorderService
    ) {
    }

    public function validate(
        Quote $quote,
        int $productId,
        float $requestedQuantity,
        bool $isQuoteItemId,
        array $quoteItemsQuantity
    ): void {
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        $maxQuantity = 0;
        $name = '';
        if ($isQuoteItemId) {
            $quoteItem = $quote->getItemById($productId);
            if ($quoteItem) {
                $name = $quoteItem->getName();
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
                throw new NoSuchEntityException(__('Product ID: %1 not found.', $productId));
            }

            $quoteItem = $quote->getItemByProduct($product);
            $name = $product->getName();
            $itemQty = 0;
            if ($quoteItem instanceof Item) {
                $itemQty = $quoteItem->getQty();
            }

            $stockId = (int)$this->stockByWebsiteIdResolver->execute($websiteId)->getStockId();
            $stockItemConfiguration = $this->getStockItemConfiguration->execute($product->getSku(), $stockId);
            $stockQuantity = $this->getSimpleProductStockQuantity($stockId, $product, $requestedQuantity);
            $maxQuantity = min([$stockItemConfiguration->getMaxSaleQty(), $stockQuantity]);
            if ($quoteItemsQuantity && isset($quoteItemsQuantity[$product->getId()])) {
                $maxQuantity -= ($quoteItemsQuantity[$product->getId()] - $itemQty);
            }
            $maxQuantity = (float)$maxQuantity;
        }

        if ($requestedQuantity > $maxQuantity) {
            throw new QuoteItemOutOfStockException(
                __(
                    'Item "%1" is no longer available in requested quantity: %2. Currently available: %3',
                    $name,
                    $requestedQuantity,
                    $maxQuantity
                )
            );
        }
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
            if ($quoteItemsQuantity && isset($quoteItemsQuantity[$child->getProduct()->getId()])) {
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

        $unmanagedStock = $this->manageStockCondition->execute($product->getSku(), $stockId);
        $isBackOrdered = $this->productBackorderService->isProductBackOrdered($product, $stockId);

        if ($unmanagedStock || $isBackOrdered) {
            $stockQuantity = $this->productBackorderService->getBackOrderMaxSalesQty($product, $stockId);
        }

        return (float)$stockQuantity;
    }
}
