<?php
declare(strict_types=1);

namespace InPost\InPostPay\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;

interface InPostPayBestsellerProductRepositoryInterface
{
    /**
     * @param InPostPayBestsellerProductInterface $inPostPayBestsellerProduct
     * @return InPostPayBestsellerProductInterface
     * @throws CouldNotSaveException
     */
    public function save(
        InPostPayBestsellerProductInterface $inPostPayBestsellerProduct
    ): InPostPayBestsellerProductInterface;

    /**
     * @param int $id
     * @return InPostPayBestsellerProductInterface
     * @throws NoSuchEntityException
     */
    public function get(int $id): InPostPayBestsellerProductInterface;

    /**
     * @param string $sku
     * @param int $websiteId
     * @return InPostPayBestsellerProductInterface
     * @throws NoSuchEntityException
     */
    public function getBySkuAndWebsiteId(string $sku, int $websiteId): InPostPayBestsellerProductInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults;

    /**
     * @param InPostPayBestsellerProductInterface $inPostPayBestsellerProduct
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(InPostPayBestsellerProductInterface $inPostPayBestsellerProduct): bool;

    /**
     * @param int $id
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $id): bool;

    /**
     * @return void
     */
    public function clearInstancesCache(): void;
}
