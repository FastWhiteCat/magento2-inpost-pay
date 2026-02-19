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
    private array $inPostPayOrdersByIds = [];
    private array $inPostPayOrdersByOrderIds = [];

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

            if (isset($this->inPostPayOrdersByIds[$inPostPayOrder->getOrderId()])) {
                unset($this->inPostPayOrdersByIds[$inPostPayOrder->getOrderId()]);
            }

            if (isset($this->inPostPayOrdersByOrderIds[(int)$inPostPayOrder->getInPostPayOrderId()])) {
                unset($this->inPostPayOrdersByOrderIds[(int)$inPostPayOrder->getInPostPayOrderId()]);
            }
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not save InPost Pay Order: %1', $e->getMessage()));
        }

        return $inPostPayOrder;
    }

    public function get(int $inPostPayOrderId, bool $forceReload = false): InPostPayOrderInterface
    {
        if (!$forceReload
            && isset($this->inPostPayOrdersByIds[$inPostPayOrderId])
            && $this->inPostPayOrdersByIds[$inPostPayOrderId] instanceof InPostPayOrderInterface
        ) {
            return $this->inPostPayOrdersByIds[$inPostPayOrderId];
        }

        $inPostPayOrder = $this->inPostPayOrderInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($inPostPayOrder, $inPostPayOrderId);
        if (!$inPostPayOrder->getInPostPayOrderId()) {
            throw new NoSuchEntityException(__('InPost Pay Order with ID "%1" does not exist.', $inPostPayOrderId));
        }

        $this->inPostPayOrdersByIds[(int)$inPostPayOrder->getInPostPayOrderId()] = $inPostPayOrder;
        $this->inPostPayOrdersByOrderIds[$inPostPayOrder->getOrderId()] = $inPostPayOrder;

        return $inPostPayOrder;
    }

    public function getByOrderId(int $orderId, bool $forceReload = false): InPostPayOrderInterface
    {
        if (!$forceReload
            && isset($this->inPostPayOrdersByOrderIds[$orderId])
            && $this->inPostPayOrdersByOrderIds[$orderId] instanceof InPostPayOrderInterface
        ) {
            return $this->inPostPayOrdersByOrderIds[$orderId];
        }

        /** @var InPostPayOrderInterface $inPostPayOrder */
        $inPostPayOrder = $this->inPostPayOrderInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($inPostPayOrder, $orderId, InPostPayOrderInterface::ORDER_ID);
        if (!$inPostPayOrder->getInPostPayOrderId()) {
            throw new NoSuchEntityException(__('InPost Pay Order with Order ID "%1" does not exist.', $orderId));
        }

        $this->inPostPayOrdersByIds[(int)$inPostPayOrder->getInPostPayOrderId()] = $inPostPayOrder;
        $this->inPostPayOrdersByOrderIds[$inPostPayOrder->getOrderId()] = $inPostPayOrder;

        return $inPostPayOrder;
    }

    public function getByBasketBindingApiKey(string $basketBindingApiKey): InPostPayOrderInterface
    {
        /** @var InPostPayOrderInterface $inPostPayOrder */
        $inPostPayOrder = $this->inPostPayOrderInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($inPostPayOrder, $basketBindingApiKey, InPostPayOrderInterface::BASKET_BINDING_API_KEY);
        if (!$inPostPayOrder->getInPostPayOrderId()) {
            throw new NoSuchEntityException(
                __('InPost Pay Order with Basket Binding API Key "%1" does not exist.', $basketBindingApiKey)
            );
        }

        $this->inPostPayOrdersByIds[(int)$inPostPayOrder->getInPostPayOrderId()] = $inPostPayOrder;
        $this->inPostPayOrdersByOrderIds[$inPostPayOrder->getOrderId()] = $inPostPayOrder;

        return $inPostPayOrder;
    }

    public function getByBasketId(string $basketId): InPostPayOrderInterface
    {
        /** @var InPostPayOrderInterface $inPostPayOrder */
        $inPostPayOrder = $this->inPostPayOrderInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($inPostPayOrder, $basketId, InPostPayOrderInterface::BASKET_ID);
        if (!$inPostPayOrder->getInPostPayOrderId()) {
            throw new NoSuchEntityException(__('InPost Pay Order with Basket ID "%1" does not exist.', $basketId));
        }

        return $inPostPayOrder;
    }

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
            $orderId = $inPostPayOrder->getOrderId();
            $inPostPayOrderId = (int)$inPostPayOrder->getInPostPayOrderId();
            // @phpstan-ignore-next-line
            $this->resource->delete($inPostPayOrder);

            if (isset($this->inPostPayOrdersByIds[$inPostPayOrderId])) {
                unset($this->inPostPayOrdersByIds[$inPostPayOrderId]);
            }

            if (isset($this->inPostPayOrdersByOrderIds[$orderId])) {
                unset($this->inPostPayOrdersByOrderIds[$orderId]);
            }
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
