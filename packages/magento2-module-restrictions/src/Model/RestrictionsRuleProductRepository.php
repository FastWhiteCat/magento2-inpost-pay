<?php

declare(strict_types=1);

namespace InPost\Restrictions\Model;

use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\Restrictions\Api\Data\RestrictionsRuleProductInterface;
use InPost\Restrictions\Api\Data\RestrictionsRuleProductInterfaceFactory;
use Magento\Framework\Api\SearchResultsFactory;
use InPost\Restrictions\Api\RestrictionsRuleProductRepositoryInterface;
use InPost\Restrictions\Model\ResourceModel\RestrictionsRuleProduct as RestrictionsRuleProductResource;
use InPost\Restrictions\Model\ResourceModel\RestrictionsRuleProduct\CollectionFactory;

class RestrictionsRuleProductRepository implements RestrictionsRuleProductRepositoryInterface
{
    /**
     * @param RestrictionsRuleProductResource $resource
     * @param RestrictionsRuleProductInterfaceFactory $restrictionsRuleProductFactory
     * @param CollectionFactory $restrictionsRuleProductCollectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        private readonly RestrictionsRuleProductResource $resource,
        private readonly RestrictionsRuleProductInterfaceFactory $restrictionsRuleProductFactory,
        private readonly CollectionFactory $restrictionsRuleProductCollectionFactory,
        private readonly SearchResultsFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    public function save(RestrictionsRuleProductInterface $restrictionsRuleProduct): RestrictionsRuleProductInterface
    {
        try {
            // @phpstan-ignore-next-line
            $this->resource->save($restrictionsRuleProduct);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the Restriction Rule Product: %1', $exception->getMessage())
            );
        }
        return $restrictionsRuleProduct;
    }

    public function get(int $indexId): RestrictionsRuleProductInterface
    {
        $restrictionsRuleProduct = $this->restrictionsRuleProductFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($restrictionsRuleProduct, $indexId);
        if (!$restrictionsRuleProduct->getRuleId()) {
            throw new NoSuchEntityException(
                __('Restrictions Rule Product with ID "%1" does not exist.', $indexId)
            );
        }
        return $restrictionsRuleProduct;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults
    {
        $collection = $this->restrictionsRuleProductCollectionFactory->create();
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

    public function delete(RestrictionsRuleProductInterface $restrictionsRuleProduct): bool
    {
        try {
            // @phpstan-ignore-next-line
            $this->resource->delete($restrictionsRuleProduct);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the Restrictions Rule Product: %1', $exception->getMessage())
            );
        }
        return true;
    }

    public function deleteById(int $indexId): bool
    {
        return $this->delete($this->get($indexId));
    }
}
