<?php

declare(strict_types=1);

namespace InPost\Restrictions\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;

interface RestrictionsRuleRepositoryInterface
{
    /**
     * @param RestrictionsRuleInterface $restrictionsRule
     * @return RestrictionsRuleInterface
     * @throws LocalizedException
     */
    public function save(
        RestrictionsRuleInterface $restrictionsRule
    ): RestrictionsRuleInterface;

    /**
     * @param int $ruleId
     * @return RestrictionsRuleInterface
     * @throws LocalizedException
     */
    public function get(int $ruleId): RestrictionsRuleInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults;

    /**
     * @param RestrictionsRuleInterface $restrictionsRule
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(RestrictionsRuleInterface $restrictionsRule): bool;

    /**
     * @param int $ruleId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $ruleId): bool;
}
