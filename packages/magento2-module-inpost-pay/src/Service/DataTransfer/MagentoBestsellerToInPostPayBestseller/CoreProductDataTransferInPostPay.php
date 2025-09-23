<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\MagentoBestsellerToInPostPayBestseller;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Api\DataTransfer\MagentoBestsellerToInPostPayBestsellerDataTransferInterface;
use InPost\InPostPay\Exception\InvalidBestsellerProductDataException;
use InPost\InPostPay\Provider\Product\BestsellerProductEanProvider;
use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface as InPostProduct;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterfaceFactory as InPostProductFactory;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\ProductAvailabilityInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\ProductAvailabilityInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Website;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CoreProductDataTransferInPostPay implements MagentoBestsellerToInPostPayBestsellerDataTransferInterface
{
    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param ProductToInPostProductDataTransfer $productToInPostProductDataTransfer
     * @param InPostProductFactory $inPostProductFactory
     * @param ProductAvailabilityInterfaceFactory $productAvailabilityFactory
     * @param BestsellerProductEanProvider $bestsellerProductEanProvider
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly ProductToInPostProductDataTransfer $productToInPostProductDataTransfer,
        private readonly InPostProductFactory $inPostProductFactory,
        private readonly ProductAvailabilityInterfaceFactory $productAvailabilityFactory,
        private readonly BestsellerProductEanProvider $bestsellerProductEanProvider
    ) {
    }

    /**
     * @param InPostPayBestsellerProductInterface $magentoBestsellerProduct
     * @param BestsellerProductInterface $bestsellerProduct
     * @return void
     * @throws NoSuchEntityException
     * @throws InvalidBestsellerProductDataException
     */
    public function transfer(
        InPostPayBestsellerProductInterface $magentoBestsellerProduct,
        BestsellerProductInterface $bestsellerProduct
    ): void {
        $product = $this->getMagentoProductBySkuAndWebsiteId(
            $magentoBestsellerProduct->getSku(),
            $magentoBestsellerProduct->getWebsiteId()
        );
        $inPostProduct = $this->initInPostProduct(
            $product,
            $magentoBestsellerProduct->getWebsiteId()
        );

        $bestsellerProduct->setProductId($inPostProduct->getProductId());
        $bestsellerProduct->setEan($this->bestsellerProductEanProvider->get($product));
        $bestsellerProduct->setProductName($inPostProduct->getProductName());
        $bestsellerProduct->setProductDescription($inPostProduct->getProductDescription());
        $bestsellerProduct->setProductAttributes($inPostProduct->getProductAttributes());
        $bestsellerProduct->setProductImage($inPostProduct->getProductImage());
        $bestsellerProduct->setAdditionalProductImages($inPostProduct->getAdditionalProductImages());
        $this->transferQuantityData($product, $inPostProduct, $bestsellerProduct);
        $this->transferAvailabilityData($magentoBestsellerProduct, $bestsellerProduct);
    }

    /**
     * @param Product $product
     * @param int $websiteId
     * @return InPostProduct
     * @throws NoSuchEntityException
     */
    private function initInPostProduct(Product $product, int $websiteId): InPostProduct
    {
        /** @var InPostProduct $inPostProduct */
        $inPostProduct = $this->inPostProductFactory->create();
        $this->productToInPostProductDataTransfer->transfer($product, $inPostProduct, $websiteId, 1);

        return $inPostProduct;
    }

    /**
     * @param string $sku
     * @param int $websiteId
     * @return Product
     * @throws NoSuchEntityException
     */
    private function getMagentoProductBySkuAndWebsiteId(string $sku, int $websiteId): Product
    {
        try {
            /** @var Website $website */
            $website = $this->storeManager->getWebsite($websiteId);
            $storeId = $website->getDefaultStore()->getStoreId();
        } catch (LocalizedException $e) {
            $storeId = 0;
        }

        /** @var Product $product */
        $product = $this->productRepository->get($sku, false, $storeId, true);
        $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getAmount()->getValue();

        return $product;
    }

    /**
     * @param InPostPayBestsellerProductInterface $magentoBestsellerProduct
     * @param BestsellerProductInterface $bestsellerProduct
     * @return void
     */
    private function transferAvailabilityData(
        InPostPayBestsellerProductInterface $magentoBestsellerProduct,
        BestsellerProductInterface $bestsellerProduct
    ): void {
        /** @var ProductAvailabilityInterface $productAvailability */
        $productAvailability = $this->productAvailabilityFactory->create();

        if ($magentoBestsellerProduct->getAvailableStartDate()) {
            $productAvailability->setStartDate(
                $this->convertDateToInPostPayFormat(
                    (string)$magentoBestsellerProduct->getAvailableStartDate()
                )
            );
        }

        if ($magentoBestsellerProduct->getAvailableEndDate()) {
            $productAvailability->setEndDate(
                $this->convertDateToInPostPayFormat(
                    (string)$magentoBestsellerProduct->getAvailableEndDate()
                )
            );
        }

        if ($productAvailability->getStartDate() || $productAvailability->getEndDate()) {
            $bestsellerProduct->setProductAvailability($productAvailability);
        }
    }

    /**
     * @param Product $product
     * @param InPostProduct $inPostProduct
     * @param BestsellerProductInterface $bestsellerProduct
     * @return void
     */
    private function transferQuantityData(
        Product $product,
        InPostProduct $inPostProduct,
        BestsellerProductInterface $bestsellerProduct
    ): void {
        $bestsellerQuantity = $bestsellerProduct->getQuantity();
        $bestsellerQuantity->setQuantityType($inPostProduct->getQuantity()->getQuantityType());
        $bestsellerQuantity->setQuantityUnit($inPostProduct->getQuantity()->getQuantityUnit());

        if ($product->isSaleable()) {
            $bestsellerQuantity->setAvailableQuantity($inPostProduct->getQuantity()->getAvailableQuantity());
        } else {
            $bestsellerQuantity->setAvailableQuantity(0);
        }

        $bestsellerProduct->setQuantity($bestsellerQuantity);
    }

    /**
     * @param string $originalDate
     * @return string
     */
    private function convertDateToInPostPayFormat(string $originalDate): string
    {
        $strToTime = strtotime($originalDate);

        return $strToTime ? date(BasketInterface::INPOST_DATE_FORMAT, $strToTime) : '';
    }
}
