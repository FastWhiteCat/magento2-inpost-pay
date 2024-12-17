<?php

declare(strict_types=1);

namespace InPost\Restrictions\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\Restrictions\Api\Data\RestrictionsRuleProductInterface;

interface RestrictionsRuleProductRepositoryInterface
{
    /**
     * @param RestrictionsRuleProductInterface $restrictionsRuleProduct
     * @return RestrictionsRuleProductInterface
     * @throws LocalizedException
     */
    public function save(
        RestrictionsRuleProductInterface $restrictionsRuleProduct
    ): RestrictionsRuleProductInterface;

    /**
     * @param int $indexId
     * @return RestrictionsRuleProductInterface
     * @throws LocalizedException
     */
    public function get(int $indexId): RestrictionsRuleProductInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults;

    /**
     * @param RestrictionsRuleProductInterface $restrictionsRuleProduct
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(RestrictionsRuleProductInterface $restrictionsRuleProduct): bool;

    /**
     * @param int $indexId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $indexId): bool;
}
