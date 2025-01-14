<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\MagentoBestsellerToInPostPayBestseller;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Api\DataTransfer\MagentoBestsellerToInPostPayBestsellerDataTransferInterface;
use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface as InPostProduct;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterfaceFactory as InPostProductFactory;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\ProductAvailabilityInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\ProductAvailabilityInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;

class CoreProductDataTransferInPostPay implements MagentoBestsellerToInPostPayBestsellerDataTransferInterface
{
    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ProductToInPostProductDataTransfer $productToInPostProductDataTransfer
     * @param InPostProductFactory $inPostProductFactory
     * @param ProductAvailabilityInterfaceFactory $productAvailabilityFactory
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductToInPostProductDataTransfer $productToInPostProductDataTransfer,
        private readonly InPostProductFactory $inPostProductFactory,
        private readonly ProductAvailabilityInterfaceFactory $productAvailabilityFactory
    ) {
    }

    /**
     * @param InPostPayBestsellerProductInterface $magentoBestsellerProduct
     * @param BestsellerProductInterface $bestsellerProduct
     * @return void
     * @throws NoSuchEntityException
     */
    public function transfer(
        InPostPayBestsellerProductInterface $magentoBestsellerProduct,
        BestsellerProductInterface $bestsellerProduct
    ): void {
        $inPostProduct = $this->initInPostProduct(
            $magentoBestsellerProduct->getSku(),
            $magentoBestsellerProduct->getWebsiteId()
        );

        $bestsellerProduct->setProductId($inPostProduct->getProductId());
        $bestsellerProduct->setEan($inPostProduct->getEan());
        $bestsellerProduct->setProductName($inPostProduct->getProductName());
        $bestsellerProduct->setProductDescription($inPostProduct->getProductDescription());
        $bestsellerProduct->setProductAttributes($inPostProduct->getProductAttributes());
        $bestsellerProduct->setProductImage($inPostProduct->getProductImage());
        $this->transferQuantityData($inPostProduct, $bestsellerProduct);
        $this->transferAvailabilityData($magentoBestsellerProduct, $bestsellerProduct);
    }

    /**
     * @param string $sku
     * @param int $websiteId
     * @return InPostProduct
     * @throws NoSuchEntityException
     */
    private function initInPostProduct(string $sku, int $websiteId): InPostProduct
    {
        /** @var Product $product */
        $product = $this->productRepository->get($sku);

        /** @var InPostProduct $inPostProduct */
        $inPostProduct = $this->inPostProductFactory->create();
        $this->productToInPostProductDataTransfer->transfer($product, $inPostProduct, $websiteId, 1);

        return $inPostProduct;
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
                    $magentoBestsellerProduct->getAvailableStartDate()
                )
            );
        }

        if ($magentoBestsellerProduct->getAvailableEndDate()) {
            $productAvailability->setEndDate(
                $this->convertDateToInPostPayFormat(
                    $magentoBestsellerProduct->getAvailableEndDate()
                )
            );
        }

        if ($productAvailability->getStartDate() || $productAvailability->getEndDate()) {
            $bestsellerProduct->setProductAvailability($productAvailability);
        }
    }

    /**
     * @param InPostProduct $inPostProduct
     * @param BestsellerProductInterface $bestsellerProduct
     * @return void
     */
    private function transferQuantityData(
        InPostProduct $inPostProduct,
        BestsellerProductInterface $bestsellerProduct
    ): void {
        $bestsellerQuantity = $bestsellerProduct->getQuantity();
        $bestsellerQuantity->setQuantityType($inPostProduct->getQuantity()->getQuantityType());
        $bestsellerQuantity->setQuantityUnit($inPostProduct->getQuantity()->getQuantityUnit());
        $bestsellerQuantity->setAvailableQuantity($inPostProduct->getQuantity()->getAvailableQuantity());
        $bestsellerProduct->setQuantity($bestsellerQuantity);
    }

    /**
     * @param string|null $originalDate
     * @return string
     */
    private function convertDateToInPostPayFormat(?string $originalDate): string
    {
        return date(BasketInterface::INPOST_DATE_FORMAT, strtotime($originalDate));
    }
}
