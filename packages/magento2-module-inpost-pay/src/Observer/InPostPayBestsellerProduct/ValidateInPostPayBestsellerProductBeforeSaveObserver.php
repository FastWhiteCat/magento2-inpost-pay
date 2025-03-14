<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\InPostPayBestsellerProduct;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Exception\InvalidBestsellerProductDataException;
use InPost\InPostPay\Model\InPostPayBestsellerProductRepository;
use InPost\InPostPay\Model\Source\Store\BestsellerProductPriority;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

class ValidateInPostPayBestsellerProductBeforeSaveObserver implements ObserverInterface
{
    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param InPostPayBestsellerProductRepository $inPostPayBestsellerProductRepository
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly InPostPayBestsellerProductRepository $inPostPayBestsellerProductRepository
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws InvalidBestsellerProductDataException
     */
    public function execute(Observer $observer): void
    {
        $bestsellerProduct = $observer->getEvent()->getData(InPostPayBestsellerProductInterface::ENTITY_NAME);

        if ($bestsellerProduct instanceof InPostPayBestsellerProductInterface) {
            $this->validatePriority($bestsellerProduct);
            $this->validateSku($bestsellerProduct);
            $this->validatePriorityAndWebsiteConflict($bestsellerProduct);
            $this->validateSkuAndWebsiteConflict($bestsellerProduct);
        }
    }

    /**
     * @param InPostPayBestsellerProductInterface $bestsellerProduct
     * @return void
     * @throws InvalidBestsellerProductDataException
     */
    private function validatePriority(InPostPayBestsellerProductInterface $bestsellerProduct): void
    {
        if ($bestsellerProduct->getPriority() < BestsellerProductPriority::MIN_PRIORITY
            || $bestsellerProduct->getPriority() > BestsellerProductPriority::MAX_PRIORITY
        ) {
            throw new InvalidBestsellerProductDataException(
                __(
                    'InPost Pay Bestseller Product must have a priority value between 1 and 5. %1 given.',
                    $bestsellerProduct->getPriority()
                )
            );
        }
    }

    /**
     * @param InPostPayBestsellerProductInterface $bestsellerProduct
     * @return void
     * @throws InvalidBestsellerProductDataException
     */
    private function validateSku(InPostPayBestsellerProductInterface $bestsellerProduct): void
    {
        try {
            /** @var Website $website */
            $website = $this->storeManager->getWebsite($bestsellerProduct->getWebsiteId());
            $defaultStoreId = (int)$website->getDefaultStore()->getId();
        } catch (LocalizedException $e) {
            $defaultStoreId = 0;
        }

        try {
            /** @var Product $product */
            $product = $this->productRepository->get($bestsellerProduct->getSku(), false, $defaultStoreId);
        } catch (NoSuchEntityException $e) {
            throw new InvalidBestsellerProductDataException(
                __('Product with SKU:%1 does not exist.', $bestsellerProduct->getSku())
            );
        }

        if ($bestsellerProduct->getBestsellerProductId() === null && !$product->isSalable()) {
            throw new InvalidBestsellerProductDataException(
                __('Product "%1" is currently not available for sale.', $product->getName())
            );
        }

        $productTypeId = is_string($product->getTypeId()) ? (string)$product->getTypeId() : null;

        if (in_array($productTypeId, ['configurable', 'grouped', 'bundle'], true)) {
            throw new InvalidBestsellerProductDataException(
                __(
                    'InPost Bestsellers currently cannot handle bundle, configurable or grouped product types.'
                )
            );
        }
    }

    /**
     * @param InPostPayBestsellerProductInterface $bestsellerProduct
     * @return void
     * @throws InvalidBestsellerProductDataException
     */
    private function validatePriorityAndWebsiteConflict(InPostPayBestsellerProductInterface $bestsellerProduct): void
    {
        try {
            $existingRecord = $this->inPostPayBestsellerProductRepository->getByWebsiteIdAndPriority(
                $bestsellerProduct->getWebsiteId(),
                $bestsellerProduct->getPriority()
            );

            if ($bestsellerProduct->getBestsellerProductId() !== $existingRecord->getBestsellerProductId()) {
                $errorMsg = __(
                    'Bestseller with Priority:%1 for Website ID:%2 already exists. Remove or edit that record.',
                    $existingRecord->getPriority(),
                    $existingRecord->getWebsiteId()
                );
            } else {
                $errorMsg = null;
            }
        } catch (NoSuchEntityException $e) {
            $errorMsg = null;
        }

        if ($errorMsg) {
            throw new InvalidBestsellerProductDataException($errorMsg);
        }
    }

    /**
     * @param InPostPayBestsellerProductInterface $bestsellerProduct
     * @return void
     * @throws InvalidBestsellerProductDataException
     */
    private function validateSkuAndWebsiteConflict(InPostPayBestsellerProductInterface $bestsellerProduct): void
    {
        try {
            $existingRecord = $this->inPostPayBestsellerProductRepository->getBySkuAndWebsiteId(
                $bestsellerProduct->getSku(),
                $bestsellerProduct->getWebsiteId()
            );

            if ($bestsellerProduct->getBestsellerProductId() !== $existingRecord->getBestsellerProductId()) {
                $errorMsg = __(
                    'Bestseller with SKU:%1 for Website ID:%2 already exists. Remove or edit that record.',
                    $existingRecord->getSku(),
                    $existingRecord->getWebsiteId()
                );
            } else {
                $errorMsg = null;
            }
        } catch (NoSuchEntityException $e) {
            $errorMsg = null;
        }

        if ($errorMsg) {
            throw new InvalidBestsellerProductDataException($errorMsg);
        }
    }
}
