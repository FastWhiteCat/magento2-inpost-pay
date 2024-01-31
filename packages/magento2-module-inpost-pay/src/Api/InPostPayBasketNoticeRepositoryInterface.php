<?php
declare(strict_types=1);

namespace InPost\InPostPay\Api;

use InPost\InPostPay\Exception\InPostPayAuthorizationException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;

interface InPostPayBasketNoticeRepositoryInterface
{
    /**
     * @param InPostPayBasketNoticeInterface $inPostPayBasketNotice
     * @return InPostPayBasketNoticeInterface
     * @throws CouldNotSaveException
     */
    public function save(InPostPayBasketNoticeInterface $inPostPayBasketNotice): InPostPayBasketNoticeInterface;

    /**
     * @param int $id
     * @return InPostPayBasketNoticeInterface
     * @throws NoSuchEntityException
     */
    public function get(int $id): InPostPayBasketNoticeInterface;

    /**
     * @param int $inPostPayQuoteId
     * @return InPostPayBasketNoticeInterface
     * @throws NoSuchEntityException
     */
    public function getByInPostPayQuoteId(int $inPostPayQuoteId): InPostPayBasketNoticeInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults;

    /**
     * @param InPostPayBasketNoticeInterface $inPostPayBasketNotice
     * @return bool true on success
     * @throws CouldNotDeleteException
     * @throws InPostPayAuthorizationException
     */
    public function delete(InPostPayBasketNoticeInterface $inPostPayBasketNotice): bool;

    /**
     * @param int $id
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $id): bool;
}
