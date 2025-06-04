<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Product;

use Magento\CatalogInventory\Model\Stock\Item;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Observer\Product\UpdateInPostPayBestsellerProductAfterSaveObserver as ParentObserver;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateInPostPayBestsellerProductAfterStockItemSaveObserver extends ParentObserver implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var Item $item */
        $item = $observer->getEvent()->getData('item');

        if (!$this->bestsellerChecker->isBestsellerProductById($item->getProductId())) {
            return;
        }

        try {
            /** @var Product $product */
            $product = $this->productRepository->getById($item->getProductId());
        } catch (NoSuchEntityException $e) {
            return;
        }

        try {
            $this->updateBestsellerProduct($product);
            $this->skipFurtherBestsellerUploadRegistry->setSkipFurtherBestsellerUploadFlag();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
