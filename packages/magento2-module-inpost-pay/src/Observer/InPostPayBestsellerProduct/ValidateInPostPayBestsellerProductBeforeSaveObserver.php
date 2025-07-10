<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\InPostPayBestsellerProduct;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Exception\InvalidBestsellerProductDataException;
use InPost\InPostPay\Model\InPostPayBestsellerProductRepository;
use InPost\InPostPay\Validator\Bestseller\EanValidator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use InPost\InPostPay\Model\ResourceModel\InPostPayBestsellerProduct\CollectionFactory as BestsellersCollectionFactory;

class ValidateInPostPayBestsellerProductBeforeSaveObserver implements ObserverInterface
{
    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param InPostPayBestsellerProductRepository $inPostPayBestsellerProductRepository
     * @param BestsellersCollectionFactory $bestsellersCollectionFactory
     * @param EanValidator $eanValidator
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly InPostPayBestsellerProductRepository $inPostPayBestsellerProductRepository,
        private readonly BestsellersCollectionFactory $bestsellersCollectionFactory,
        private readonly EanValidator $eanValidator
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
            $this->validateSku($bestsellerProduct);
            $this->validateSkuAndWebsiteConflict($bestsellerProduct);
            $this->validateAvailableFromToDates($bestsellerProduct);
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

        $productTypeId = is_string($product->getTypeId()) ? (string)$product->getTypeId() : null;

        if (in_array($productTypeId, ['configurable', 'grouped', 'bundle'], true)) {
            throw new InvalidBestsellerProductDataException(
                __(
                    'InPost Bestsellers currently cannot handle bundle, configurable or grouped product types.'
                )
            );
        }

        $this->eanValidator->validate($product);
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

    private function validateAvailableFromToDates(InPostPayBestsellerProductInterface $bestsellerProduct): void
    {
        $availableFrom = $bestsellerProduct->getAvailableStartDate();
        $availableTo = $bestsellerProduct->getAvailableEndDate();

        if ($availableFrom && $availableTo && $availableFrom >= $availableTo) {
            throw new InvalidBestsellerProductDataException(
                __('Bad availability date range. Available end date must be greater than available start date.')
            );
        }
    }

    /**
     * @param int $websiteId
     * @return InPostPayBestsellerProductInterface[]
     */
    public function getBestsellersByWebsiteId(int $websiteId): array
    {
        $collection = $this->bestsellersCollectionFactory->create();
        $collection->addFieldToFilter(InPostPayBestsellerProductInterface::WEBSITE_ID, ['eq' => $websiteId]);
        $bestsellers = [];

        foreach ($collection->getItems() as $item) {
            if ($item instanceof InPostPayBestsellerProductInterface) {
                $bestsellers[] = $item;
            }
        }

        return $bestsellers;
    }
}
