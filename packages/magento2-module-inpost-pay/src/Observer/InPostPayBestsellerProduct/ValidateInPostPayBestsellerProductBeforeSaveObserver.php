<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\InPostPayBestsellerProduct;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Exception\InvalidBestsellerProductDataException;
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
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreManagerInterface $storeManager
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

        if (!$product->isSalable()) {
            throw new InvalidBestsellerProductDataException(
                __('Product "%1" is currently not available for sale.', $product->getName())
            );
        }
    }
}
