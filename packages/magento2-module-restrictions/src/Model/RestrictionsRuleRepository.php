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
use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Api\Data\RestrictionsRuleInterfaceFactory;
use Magento\Framework\Api\SearchResultsFactory;
use InPost\Restrictions\Api\RestrictionsRuleRepositoryInterface;
use InPost\Restrictions\Model\ResourceModel\RestrictionsRule as RestrictionsRuleResource;
use InPost\Restrictions\Model\ResourceModel\RestrictionsRule\CollectionFactory as RestrictionsRuleCollectionFactory;

class RestrictionsRuleRepository implements RestrictionsRuleRepositoryInterface
{
    /**
     * @param RestrictionsRuleResource $resource
     * @param RestrictionsRuleInterfaceFactory $restrictionsRuleFactory
     * @param RestrictionsRuleCollectionFactory $restrictionsRuleCollectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        private readonly RestrictionsRuleResource $resource,
        private readonly RestrictionsRuleInterfaceFactory $restrictionsRuleFactory,
        private readonly RestrictionsRuleCollectionFactory $restrictionsRuleCollectionFactory,
        private readonly SearchResultsFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    public function save(RestrictionsRuleInterface $restrictionsRule): RestrictionsRuleInterface
    {
        try {
            // @phpstan-ignore-next-line
            $this->resource->save($restrictionsRule);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__('Could not save the Restriction Rule: %1', $exception->getMessage()));
        }
        return $restrictionsRule;
    }

    public function get(int $ruleId): RestrictionsRuleInterface
    {
        $restrictionsRule = $this->restrictionsRuleFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($restrictionsRule, $ruleId);
        if (!$restrictionsRule->getRuleId()) {
            throw new NoSuchEntityException(__('Restrictions Rule with id "%1" does not exist.', $ruleId));
        }
        return $restrictionsRule;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults
    {
        $collection = $this->restrictionsRuleCollectionFactory->create();
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

    public function delete(RestrictionsRuleInterface $restrictionsRule): bool
    {
        try {
            // @phpstan-ignore-next-line
            $this->resource->delete($restrictionsRule);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the Restrictions Rule: %1', $exception->getMessage())
            );
        }
        return true;
    }

    public function deleteById(int $ruleId): bool
    {
        return $this->delete($this->get($ruleId));
    }
}
