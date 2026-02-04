<?php
declare(strict_types=1);

namespace InPost\InPostPay\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementStoreInterface;

interface InPostPayCheckoutAgreementStoreRepositoryInterface
{
    /**
     * @param InPostPayCheckoutAgreementStoreInterface $inPostPayCheckoutAgreementStore
     * @return InPostPayCheckoutAgreementStoreInterface
     * @throws CouldNotSaveException
     */
    public function save(
        InPostPayCheckoutAgreementStoreInterface $inPostPayCheckoutAgreementStore
    ): InPostPayCheckoutAgreementStoreInterface;

    /**
     * @param int $entityId
     * @return InPostPayCheckoutAgreementStoreInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): InPostPayCheckoutAgreementStoreInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults;

    /**
     * @param InPostPayCheckoutAgreementStoreInterface $inPostPayCheckoutAgreementStore
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(InPostPayCheckoutAgreementStoreInterface $inPostPayCheckoutAgreementStore): bool;

    /**
     * @param int $entityId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $entityId): bool;
}
