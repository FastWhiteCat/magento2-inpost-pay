<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\BestsellerProduct;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Api\InPostPayBestsellerProductRepositoryInterface;
use InPost\InPostPay\Model\ResourceModel\InPostPayBestsellerProduct\CollectionFactory as BestsellersCollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;

class Cleaner
{
    /**
     * @param BestsellersCollectionFactory $bestsellersCollectionFactory
     * @param InPostPayBestsellerProductRepositoryInterface $inPostPayBestsellerProductRepository
     */
    public function __construct(
        private readonly BestsellersCollectionFactory $bestsellersCollectionFactory,
        private readonly InPostPayBestsellerProductRepositoryInterface $inPostPayBestsellerProductRepository
    ) {
    }

    /**
     * @param int $websiteId
     * @return void
     * @throws CouldNotDeleteException
     */
    public function deleteAllMagentoBestsellerProducts(int $websiteId): void
    {
        $collection = $this->bestsellersCollectionFactory->create();
        $collection->addFieldToFilter(InPostPayBestsellerProductInterface::WEBSITE_ID, ['eq' => $websiteId]);
        $items = $collection->getItems();

        foreach ($items as $bestsellerProduct) {
            if ($bestsellerProduct instanceof InPostPayBestsellerProductInterface) {
                $bestsellerProduct->setSkipUpdateFlag(true);
                $this->inPostPayBestsellerProductRepository->delete($bestsellerProduct);
            }
        }
    }
}
