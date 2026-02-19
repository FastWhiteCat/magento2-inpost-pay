<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\BestsellerProduct;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Api\InPostPayBestsellerProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime;

class Creator
{
    /**
     * @param ProductRepositoryInterface $productRepository
     * @param InPostPayBestsellerProductInterfaceFactory $inPostPayBestsellerProductFactory
     * @param InPostPayBestsellerProductRepositoryInterface $inPostPayBestsellerProductRepository
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly InPostPayBestsellerProductInterfaceFactory $inPostPayBestsellerProductFactory,
        private readonly InPostPayBestsellerProductRepositoryInterface $inPostPayBestsellerProductRepository
    ) {
    }

    /**
     * @param int $websiteId
     * @param BestsellerProductInterface $inPostBestseller
     * @return InPostPayBestsellerProductInterface
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function createMagentoBestsellerProduct(
        int $websiteId,
        BestsellerProductInterface $inPostBestseller
    ): InPostPayBestsellerProductInterface {
        $product = $this->getProductById((int)$inPostBestseller->getProductId());
        $productAvailability = $inPostBestseller->getProductAvailability();
        $availableStartDate = null;
        $availableEndDate = null;
        $qrCode = $inPostBestseller->getQrCode();
        $deepLink = $inPostBestseller->getDeepLink();
        $inPostPayStatus = $inPostBestseller->getStatus();

        /** @var InPostPayBestsellerProductInterface $bestsellerProduct */
        $bestsellerProduct = $this->inPostPayBestsellerProductFactory->create();
        $bestsellerProduct->setWebsiteId($websiteId);
        $bestsellerProduct->setSku($product->getSku());

        if ($productAvailability) {
            $availableStartDate = $productAvailability->getStartDate();
            $availableEndDate = $productAvailability->getEndDate();
        }

        $bestsellerProduct->setAvailableStartDate($availableStartDate);
        $bestsellerProduct->setAvailableEndDate($availableEndDate);
        $bestsellerProduct->setSynchronizedAt(date(DateTime::DATETIME_PHP_FORMAT));
        $bestsellerProduct->setQrCode($qrCode);
        $bestsellerProduct->setDeepLink($deepLink);
        $bestsellerProduct->setInPostPayStatus($inPostPayStatus);

        return $this->inPostPayBestsellerProductRepository->save($bestsellerProduct);
    }

    /**
     * @param int $productId
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    private function getProductById(int $productId): ProductInterface
    {
        return $this->productRepository->getById($productId);
    }
}
