<?php
declare(strict_types=1);

namespace InPost\InPostPay\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;

interface InPostPayCheckoutAgreementRepositoryInterface
{
    /**
     * @param InPostPayCheckoutAgreementInterface $inPostPayCheckoutAgreement
     * @return InPostPayCheckoutAgreementInterface
     * @throws CouldNotSaveException
     */
    public function save(
        InPostPayCheckoutAgreementInterface $inPostPayCheckoutAgreement
    ): InPostPayCheckoutAgreementInterface;

    /**
     * @param int $agreementId
     * @return InPostPayCheckoutAgreementInterface
     * @throws NoSuchEntityException
     */
    public function get(int $agreementId): InPostPayCheckoutAgreementInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults;

    /**
     * @param InPostPayCheckoutAgreementInterface $inPostPayCheckoutAgreement
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(InPostPayCheckoutAgreementInterface $inPostPayCheckoutAgreement): bool;

    /**
     * @param int $agreementId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $agreementId): bool;
}
