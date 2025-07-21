<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Service\DataTransfer\BestsellerProductDataTransfer;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use InPost\InPostPay\Model\ResourceModel\InPostPayBestsellerProduct\CollectionFactory
    as InPostPayBestsellerProductCollectionFactory;
use InPost\InPostPay\Model\ResourceModel\InPostPayBestsellerProduct\Collection
    as InPostPayBestsellerProductCollection;
use InPost\InPostPay\Api\Data\Merchant\BestsellersInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellersInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterfaceFactory;

class BestsellersProvider
{
    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param InPostPayBestsellerProductCollectionFactory $inPostPayBestsellerProductCollectionFactory
     * @param BestsellerProductInterfaceFactory $bestsellerProductFactory
     * @param BestsellerProductDataTransfer $bestsellerProductDataTransfer
     * @param BestsellersInterfaceFactory $bestsellersFactory
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly InPostPayBestsellerProductCollectionFactory $inPostPayBestsellerProductCollectionFactory,
        private readonly BestsellerProductInterfaceFactory $bestsellerProductFactory,
        private readonly BestsellerProductDataTransfer $bestsellerProductDataTransfer,
        private readonly BestsellersInterfaceFactory $bestsellersFactory,
    ) {
    }

    /**
     * @param int $pageIndex
     * @param int $pageSize
     * @param int|null $productId
     * @return BestsellersInterface
     * @throws NoSuchEntityException
     */
    public function get(
        int $pageIndex,
        int $pageSize,
        ?int $productId,
    ): BestsellersInterface {
        $websiteId = (int)$this->storeManager->getStore()->getWebsiteId();

        /** @var InPostPayBestsellerProductCollection $collection */
        $collection = $this->inPostPayBestsellerProductCollectionFactory->create();
        $collection->addFieldToFilter(InPostPayBestsellerProductInterface::WEBSITE_ID, ['eq' => $websiteId]);

        if ($productId) {
            $collection->addFieldToFilter(
                InPostPayBestsellerProductInterface::SKU,
                $this->getProductById($productId)->getSku()
            );

            $totalCount = 1;
            $pageIndex = 1;
            $pageSize = 1;
        } else {
            $totalCount = $this->getTotalCount($websiteId);
            $pageIndex = $pageIndex ? $pageIndex : 1;
            $pageSize = $pageSize ? $pageSize : 1;
        }

        $collection->setPageSize($pageSize);
        $collection->setCurPage($pageIndex);

        return $this->prepareResult(
            $collection->getItems(),
            $totalCount,
            $pageIndex,
            $pageSize
        );
    }

    /**
     * @param array $bestsellers
     * @param int $totalCount
     * @param int $pageIndex
     * @param int $pageSize
     * @return BestsellersInterface
     */
    private function prepareResult(
        array $bestsellers,
        int $totalCount,
        int $pageIndex,
        int $pageSize
    ): BestsellersInterface {
        $items = [];

        foreach ($bestsellers as $magentoBestsellerProduct) {
            if (!$magentoBestsellerProduct instanceof InPostPayBestsellerProductInterface) {
                continue;
            }

            $bestsellerProduct = $this->bestsellerProductFactory->create();
            $this->bestsellerProductDataTransfer->transfer(
                $magentoBestsellerProduct,
                $bestsellerProduct
            );

            $items[] = $bestsellerProduct;
        }

        /** @var BestsellersInterface $result */
        $result = $this->bestsellersFactory->create();
        $result->setPageIndex($pageIndex);
        $result->setPageSize($pageSize);
        $result->setTotalItems($totalCount);
        $result->setContent($items);

        return $result;
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

    /**
     * @param int $websiteId
     * @return int
     * @throws NoSuchEntityException
     */
    private function getTotalCount(int $websiteId): int
    {
        /** @var InPostPayBestsellerProductCollection $collection */
        $collection = $this->inPostPayBestsellerProductCollectionFactory->create();
        $collection->addFieldToFilter(InPostPayBestsellerProductInterface::WEBSITE_ID, ['eq' => $websiteId]);

        return $collection->getSize();
    }
}
