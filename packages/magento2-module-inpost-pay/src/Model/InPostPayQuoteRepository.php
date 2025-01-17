<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterfaceFactory;
use Magento\Framework\Api\SearchResultsFactory;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Model\ResourceModel\InPostPayQuote as InPostPayQuoteResource;
use InPost\InPostPay\Model\ResourceModel\InPostPayQuote\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InPostPayQuoteRepository implements InPostPayQuoteRepositoryInterface
{
    public function __construct(
        private readonly CollectionProcessorInterface $collectionProcessor,
        private readonly InPostPayQuoteResource $resource,
        private readonly InPostPayQuoteInterfaceFactory $inPostPayQuoteInterfaceFactory,
        private readonly CollectionFactory $inPostPayQuoteCollectionFactory,
        private readonly SearchResultsFactory $searchResultsFactory
    ) {
    }

    public function save(InPostPayQuoteInterface $inPostPayQuote): InPostPayQuoteInterface
    {
        try {
            // @phpstan-ignore-next-line
            $this->resource->save($inPostPayQuote);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not save InPost Pay Quote: %1', $e->getMessage()));
        }

        return $inPostPayQuote;
    }

    public function get(int $inPostPayQuoteId): InPostPayQuoteInterface
    {
        $inPostPayQuote = $this->inPostPayQuoteInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($inPostPayQuote, $inPostPayQuoteId);
        try {
            $inPostPayQuote->getQuoteId();
        } catch (LocalizedException $e) {
            throw new NoSuchEntityException(
                __('InPost Pay Quote with ID "%1" does not exist.', $inPostPayQuoteId)
            );
        }

        return $inPostPayQuote;
    }

    public function getByQuoteId(int $quoteId): InPostPayQuoteInterface
    {
        $inPostPayQuote = $this->inPostPayQuoteInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($inPostPayQuote, $quoteId, InPostPayQuoteInterface::QUOTE_ID);
        try {
            $inPostPayQuote->getQuoteId();
        } catch (LocalizedException $e) {
            throw new NoSuchEntityException(__('InPost Pay Quote with Quote ID "%1" does not exist.', $quoteId));
        }

        return $inPostPayQuote;
    }

    public function getByBasketBindingApiKey(string $basketBindingApiKey): InPostPayQuoteInterface
    {
        $inPostPayQuote = $this->inPostPayQuoteInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($inPostPayQuote, $basketBindingApiKey, InPostPayQuoteInterface::BASKET_BINDING_API_KEY);
        try {
            $inPostPayQuote->getQuoteId();
        } catch (LocalizedException $e) {
            throw new NoSuchEntityException(
                __('InPost Pay Quote with Basket Binding API Key "%1" does not exist.', $basketBindingApiKey)
            );
        }

        return $inPostPayQuote;
    }

    public function getByBasketId(string $basketId): InPostPayQuoteInterface
    {
        $inPostPayQuote = $this->inPostPayQuoteInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($inPostPayQuote, $basketId, InPostPayQuoteInterface::BASKET_ID);
        try {
            $inPostPayQuote->getQuoteId();
        } catch (LocalizedException $e) {
            throw new NoSuchEntityException(
                __('InPost Pay Quote with Basket ID "%1" does not exist.', $basketId)
            );
        }

        return $inPostPayQuote;
    }

    public function getByInPostBasketId(string $inPostBasketId): InPostPayQuoteInterface
    {
        $inPostPayQuote = $this->inPostPayQuoteInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($inPostPayQuote, $inPostBasketId, InPostPayQuoteInterface::INPOST_BASKET_ID);
        try {
            $inPostPayQuote->getQuoteId();
        } catch (LocalizedException $e) {
            throw new NoSuchEntityException(
                __('InPost Pay Quote with InPost Basket ID "%1" does not exist.', $inPostBasketId)
            );
        }

        return $inPostPayQuote;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults
    {
        $collection = $this->inPostPayQuoteCollectionFactory->create();
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

    public function delete(InPostPayQuoteInterface $inPostPayQuote): bool
    {
        try {
            // @phpstan-ignore-next-line
            $this->resource->delete($inPostPayQuote);
        } catch (Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete InPost Pay Quote: %1', $e->getMessage()));
        }

        return true;
    }

    public function deleteById(int $id): bool
    {
        return $this->delete($this->get($id));
    }

    public function deleteByBasketId(string $basketId): bool
    {
        return $this->delete($this->getByBasketId($basketId));
    }
}
