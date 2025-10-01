<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Product;

use Exception;
use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Psr\Log\LoggerInterface;

class ProductBackorderService
{
    public function __construct(
        private readonly GetStockItemConfigurationInterface $getStockItemConfiguration,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param AbstractItem|Product $product
     * @param int $stockId
     * @return bool
     */
    public function isProductBackOrdered(AbstractItem|Product $product, int $stockId): bool
    {
        try {
            $stockItemConfiguration = $this->getStockItemConfiguration->execute($product->getSku(), $stockId);

            return $stockItemConfiguration->getBackorders() > 0;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return false;
    }

    /**
     * @param AbstractItem|Product $product
     * @param int $stockId
     * @return int
     */
    public function getBackOrderMaxSalesQty(AbstractItem|Product $product, int $stockId): int
    {
        try {
            $stockItemConfiguration = $this->getStockItemConfiguration->execute($product->getSku(), $stockId);

            return (int)$stockItemConfiguration->getMaxSaleQty();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return ProductToInPostProductDataTransfer::UNMANAGED_STOCK_QUANTITY;
    }
}
