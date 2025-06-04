<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Product;

use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Api\InPostPayBestsellerProductRepositoryInterface;
use InPost\InPostPay\Exception\NotFullySuccessfulBestsellerProductUploadException;
use InPost\InPostPay\Model\Registry\SkipFurtherBestsellerUploadRegistry;
use InPost\InPostPay\Service\BestsellerProduct\BestsellerChecker;
use InPost\InPostPay\Service\BestsellerProduct\Upload as UploadService;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterfaceFactory;
use InPost\InPostPay\Service\DataTransfer\BestsellerProductDataTransfer;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateInPostPayBestsellerProductAfterSaveObserver implements ObserverInterface
{
    public function __construct(
        protected readonly BestsellerChecker $bestsellerChecker,
        protected readonly InPostPayBestsellerProductRepositoryInterface $inPostPayBestsellerProductRepository,
        protected readonly BestsellerProductInterfaceFactory $bestsellerProductFactory,
        protected readonly BestsellerProductDataTransfer $bestsellerProductDataTransfer,
        protected readonly ProductRepositoryInterface $productRepository,
        protected readonly UploadService $uploadService,
        protected readonly StoreManagerInterface $storeManager,
        protected readonly SkipFurtherBestsellerUploadRegistry $skipFurtherBestsellerUploadRegistry,
        protected readonly LoggerInterface $logger
    ) {
    }

    public function execute(Observer $observer): void
    {
        $product = $observer->getEvent()->getData('product');

        if (!$product instanceof Product) {
            return;
        }

        if ($this->skipFurtherBestsellerUploadRegistry->canSkipFurtherBestsellerUploadFlag()
            || !$this->bestsellerChecker->isBestsellerProductBySku($product->getSku())
        ) {
            return;
        }

        try {
            $this->updateBestsellerProduct($product);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param Product $product
     * @return void
     * @throws NoSuchEntityException
     */
    public function updateBestsellerProduct(Product $product): void
    {
        $sku = (string)$product->getSku();

        /** @var Store $store */
        foreach ($this->storeManager->getStores() as $store) {
            $defaultStore = $this->getDefaultStoreOfWebsite($store) ?? $product->getStore();
            $websiteId = (int)($defaultStore->getWebsiteId() ?? 0);

            if (!$this->bestsellerChecker->isSynchronizationEnabled((int)$defaultStore->getStoreId())) {
                continue;
            }

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
                    $this->createOrUpdateInPostPayBestsellerProduct($product, $bestsellerProduct, $defaultStore);
                    $this->logger->debug(
                        sprintf(
                            'InPost Pay Bestseller product SKU: %s for Website: %s has been updated.',
                            $product->getSku(),
                            $websiteId
                        )
                    );

                    break;
                }
            } catch (NoSuchEntityException $e) {
                continue;
            } catch (NotFullySuccessfulBestsellerProductUploadException $e) {
                $this->logger->error($e->getMessage());
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
    private function createOrUpdateInPostPayBestsellerProduct(
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
                $this->uploadService->putInPostBestsellerProducts(
                    [$bestsellerProduct],
                    $store
                );
            } catch (LocalizedException $e) {
                throw new NotFullySuccessfulBestsellerProductUploadException(
                    __(
                        'Could not update InPost Pay Bestseller product SKU: %1 for Website: %2. Reason: %3',
                        $product->getSku(),
                        (int)$store->getWebsiteId(),
                        $e->getMessage()
                    )
                );
            }
        } else {
            try {
                $this->uploadService->postInPostBestsellerProducts(
                    [$bestsellerProduct],
                    $store
                );
            } catch (LocalizedException $e) {
                throw new NotFullySuccessfulBestsellerProductUploadException(
                    __(
                        'Could not create InPost Pay Bestseller product SKU: %1 for Website: %2. Reason: %3',
                        $product->getSku(),
                        (int)$store->getWebsiteId(),
                        $e->getMessage()
                    )
                );
            }
        }
    }

    /**
     * @param Store $store
     * @return Store|null
     * @throws NoSuchEntityException
     */
    protected function getDefaultStoreOfWebsite(Store $store): ?Store
    {
        $website = $store->getWebsite();

        return $website instanceof Website ? $website->getDefaultStore() : null;
    }
}
