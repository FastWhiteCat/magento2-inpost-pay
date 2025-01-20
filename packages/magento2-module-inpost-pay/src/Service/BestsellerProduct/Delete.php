<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\BestsellerProduct;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Service\ApiConnector\DeleteBestseller;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\App\Emulation as StoreEmulator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Psr\Log\LoggerInterface;

class Delete extends BestsellerProductService
{
    /**
     * @param DeleteBestseller $deleteBestseller
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param StoreEmulator $storeEmulator
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly DeleteBestseller $deleteBestseller,
        private readonly ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        StoreEmulator $storeEmulator,
        LoggerInterface $logger
    ) {
        parent::__construct($storeManager, $storeEmulator, $logger);
    }

    /**
     * @param InPostPayBestsellerProductInterface $bestsellerProduct
     * @return void
     * @throws LocalizedException
     */
    public function execute(InPostPayBestsellerProductInterface $bestsellerProduct): void
    {
        $websiteId = $bestsellerProduct->getWebsiteId();
        /** @var Website $website */
        $website = $this->storeManager->getWebsite($websiteId);
        $store = $website->getDefaultStore();

        try {
            $this->storeEmulator->startEnvironmentEmulation((int)$store->getId(), Area::AREA_FRONTEND, true);
            $product = $this->getProductBySku($bestsellerProduct->getSku());
            $this->deleteBestseller->deleteBestsellerByProductId((int)$product->getId(), (int)$store->getId());
            $this->storeEmulator->stopEnvironmentEmulation();
            $this->logger->debug(
                sprintf('Bestseller Product [SKU:%s] has been deleted from InPost Pay API.', $product->getSku())
            );
        } catch (LocalizedException $e) {
            $this->storeEmulator->stopEnvironmentEmulation();

            throw $e;
        }
    }

    /**
     * @param string $sku
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    private function getProductBySku(string $sku): ProductInterface
    {
        return $this->productRepository->get($sku);
    }
}
