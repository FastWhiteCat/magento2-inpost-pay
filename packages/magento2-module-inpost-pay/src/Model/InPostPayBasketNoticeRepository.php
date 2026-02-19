<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterfaceFactory;
use InPost\InPostPay\Api\InPostPayBasketNoticeRepositoryInterface;
use InPost\InPostPay\Model\ResourceModel\InPostPayBasketNotice as InPostPayBasketNoticeResource;
use InPost\InPostPay\Model\ResourceModel\InPostPayBasketNotice\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InPostPayBasketNoticeRepository implements InPostPayBasketNoticeRepositoryInterface
{
    public function __construct(
        private readonly CollectionProcessorInterface $collectionProcessor,
        private readonly InPostPayBasketNoticeResource $resource,
        private readonly InPostPayBasketNoticeInterfaceFactory $inPostPayBasketNoticeInterfaceFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly SearchResultsFactory $searchResultsFactory
    ) {
    }

    public function save(InPostPayBasketNoticeInterface $inPostPayBasketNotice): InPostPayBasketNoticeInterface
    {
        try {
            // @phpstan-ignore-next-line
            $this->resource->save($inPostPayBasketNotice);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not save InPost Pay Basket Notice: %1', $e->getMessage()));
        }

        return $inPostPayBasketNotice;
    }

    public function get(int $id): InPostPayBasketNoticeInterface
    {
        $inPostPayBasketNotice = $this->inPostPayBasketNoticeInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($inPostPayBasketNotice, $id);
        try {
            $inPostPayBasketNotice->getBasketNoticeId();
        } catch (LocalizedException $e) {
            throw new NoSuchEntityException(
                __('InPost Pay Basket Notice with ID "%1" does not exist.', $id)
            );
        }

        return $inPostPayBasketNotice;
    }

    public function getByInPostPayQuoteId(int $inPostPayQuoteId): InPostPayBasketNoticeInterface
    {
        $inPostPayBasketNotice = $this->inPostPayBasketNoticeInterfaceFactory->create();

        $this->resource->load(
            // @phpstan-ignore-next-line
            $inPostPayBasketNotice,
            $inPostPayQuoteId,
            InPostPayBasketNoticeInterface::INPOST_PAY_QUOTE_ID
        );

        try {
            $inPostPayBasketNotice->getBasketNoticeId();
        } catch (LocalizedException $e) {
            throw new NoSuchEntityException(
                __('InPost Pay Basket Notice with InPost Pay Quote ID "%1" does not exist.', $inPostPayQuoteId)
            );
        }

        return $inPostPayBasketNotice;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults
    {
        $collection = $this->collectionFactory->create();
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

    public function delete(InPostPayBasketNoticeInterface $inPostPayBasketNotice): bool
    {
        try {
            // @phpstan-ignore-next-line
            $this->resource->delete($inPostPayBasketNotice);
        } catch (Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not delete InPost Pay Basket Notice: %1', $e->getMessage())
            );
        }

        return true;
    }

    public function deleteById(int $id): bool
    {
        return $this->delete($this->get($id));
    }
}
