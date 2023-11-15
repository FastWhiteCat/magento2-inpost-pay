<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\InPostPayOrderInterfaceFactory;
use Magento\Framework\Api\SearchResultsFactory;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Model\ResourceModel\InPostPayOrder as InPostPayOrderResource;
use InPost\InPostPay\Model\ResourceModel\InPostPayOrder\CollectionFactory;

class InPostPayOrderRepository implements InPostPayOrderRepositoryInterface
{
    public function __construct(
        private readonly CollectionProcessorInterface $collectionProcessor,
        private readonly InPostPayOrderResource $resource,
        private readonly InPostPayOrderInterfaceFactory $inPostPayOrderInterfaceFactory,
        private readonly CollectionFactory $productSalesRestrictionLockCollectionFactory,
        private readonly SearchResultsFactory $searchResultsFactory
    ) {
    }

    public function save(InPostPayOrderInterface $inPostPayOrder): InPostPayOrderInterface
    {
        try {
            // @phpstan-ignore-next-line
            $this->resource->save($inPostPayOrder);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not save InPost Pay Order: %1', $e->getMessage()));
        }

        return $inPostPayOrder;
    }

    public function get(int $inPostPayOrderId): InPostPayOrderInterface
    {
        $inPostPayOrder = $this->inPostPayOrderInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($inPostPayOrder, $inPostPayOrderId);
        if (!$inPostPayOrder->getInPostPayOrderId()) {
            throw new NoSuchEntityException(__('InPost Pay Order with ID "%1" does not exist.', $inPostPayOrderId));
        }

        return $inPostPayOrder;
    }

    public function getByOrderId(int $orderId): InPostPayOrderInterface
    {
        $inPostPayOrder = $this->inPostPayOrderInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($inPostPayOrder, $orderId, InPostPayOrderInterface::ORDER_ID);
        if (!$inPostPayOrder->getInPostPayOrderId()) {
            throw new NoSuchEntityException(__('InPost Pay Order with Order ID "%1" does not exist.', $orderId));
        }

        return $inPostPayOrder;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults
    {
        $collection = $this->productSalesRestrictionLockCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        // @phpstan-ignore-next-line
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    public function delete(InPostPayOrderInterface $inPostPayOrder): bool
    {
        try {
            // @phpstan-ignore-next-line
            $this->resource->delete($inPostPayOrder);
        } catch (Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete InPost Pay Order: %1', $e->getMessage()));
        }

        return true;
    }

    public function deleteById(int $inPostPayOrderId): bool
    {
        return $this->delete($this->get($inPostPayOrderId));
    }
}
