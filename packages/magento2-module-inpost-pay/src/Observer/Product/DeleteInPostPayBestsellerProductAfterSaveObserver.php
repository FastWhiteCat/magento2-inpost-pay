<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Product;

use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Exception\NotFullySuccessfulBestsellerProductUploadException;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use InPost\InPostPay\Observer\Product\UpdateInPostPayBestsellerProductAfterSaveObserver as ParentObserver;
use Throwable;

class DeleteInPostPayBestsellerProductAfterSaveObserver extends ParentObserver implements ObserverInterface
{
    public function execute(Observer $observer): void
    {
        $product = $observer->getEvent()->getData('product');

        if (!$product instanceof Product) {
            return;
        }

        if (!$this->bestsellerChecker->isSynchronizationEnabled((int)$product->getStoreId())
            || !$this->bestsellerChecker->isBestsellerProductBySku($product->getSku())
        ) {
            return;
        }

        try {
            $this->deleteBestsellerProduct($product);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param Product $product
     * @return void
     * @throws NoSuchEntityException
     */
    private function deleteBestsellerProduct(Product $product): void
    {
        $sku = (string)$product->getSku();

        foreach ($this->storeManager->getStores() as $store) {
            /** @var Store $store */
            $defaultStore = $this->getDefaultStoreOfWebsite($store) ?? $product->getStore();
            $websiteId = (int)($defaultStore->getWebsiteId() ?? 0);

            try {
                $magentoBestsellerProduct = $this->inPostPayBestsellerProductRepository->getBySkuAndWebsiteId(
                    $sku,
                    $websiteId
                );

                if ($websiteId === $magentoBestsellerProduct->getWebsiteId()) {
                    /** @var BestsellerProductInterface $bestsellerProduct */
                    $bestsellerProduct = $this->bestsellerProductFactory->create();
                    $this->bestsellerProductDataTransfer->transfer(
                        $magentoBestsellerProduct,
                        $bestsellerProduct
                    );
                    $this->deleteInPostPayBestsellerProduct($product, $bestsellerProduct, $defaultStore);
                    $this->inPostPayBestsellerProductRepository->delete($magentoBestsellerProduct);
                    $this->logger->debug(
                        sprintf(
                            'InPost Pay Bestseller product SKU: %s for Website: %s has been deleted.',
                            $websiteId,
                            $store->getId()
                        )
                    );
                }
            } catch (NoSuchEntityException $e) {
                continue;
            } catch (NotFullySuccessfulBestsellerProductUploadException | CouldNotDeleteException $e) {
                $this->logger->error(
                    sprintf(
                        'Could not delete InPost Pay Bestseller product SKU: %s for Website: %s. Reason: %s',
                        $product->getSku(),
                        $websiteId,
                        $e->getMessage()
                    )
                );
            }
        }
    }

    /**
     * @param Product $product
     * @param BestsellerProductInterface $bestsellerProduct
     * @param Store $store
     * @return void
     * @throws NotFullySuccessfulBestsellerProductUploadException
     */
    private function deleteInPostPayBestsellerProduct(
        Product $product,
        BestsellerProductInterface $bestsellerProduct,
        Store $store
    ): void {
        $storeId = (int)$store->getId();
        $existingInPostPayBestsellerProductIds = $this->uploadService->getExistingInPostBestsellerProductIds(
            $storeId
        );

        if (in_array($product->getId(), $existingInPostPayBestsellerProductIds)) {
            try {
                $this->uploadService->deleteProductIdsFromInPostPay(
                    [(int)$bestsellerProduct->getProductId()],
                    $storeId
                );
            } catch (Throwable $e) {
                throw new NotFullySuccessfulBestsellerProductUploadException(__($e->getMessage()));
            }
        }
    }
}
