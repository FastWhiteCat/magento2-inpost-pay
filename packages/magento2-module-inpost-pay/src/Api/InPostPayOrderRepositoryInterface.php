<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Api\Data\InPostPayOrderInterface;

interface InPostPayOrderRepositoryInterface
{
    /**
     * @param InPostPayOrderInterface $inPostPayOrder
     * @return InPostPayOrderInterface
     * @throws CouldNotSaveException
     */
    public function save(InPostPayOrderInterface $inPostPayOrder): InPostPayOrderInterface;

    /**
     * @param int $inPostPayOrderId
     * @param bool $forceReload
     * @return InPostPayOrderInterface
     * @throws NoSuchEntityException
     */
    public function get(int $inPostPayOrderId, bool $forceReload = false): InPostPayOrderInterface;

    /**
     * @param int $orderId
     * @param bool $forceReload
     * @return InPostPayOrderInterface
     * @throws NoSuchEntityException
     */
    public function getByOrderId(int $orderId, bool $forceReload = false): InPostPayOrderInterface;

    /**
     * @param string $basketBindingApiKey
     * @return InPostPayOrderInterface
     * @throws NoSuchEntityException
     */
    public function getByBasketBindingApiKey(string $basketBindingApiKey): InPostPayOrderInterface;

    /**
     * @param string $basketId
     * @return InPostPayOrderInterface
     * @throws NoSuchEntityException
     */
    public function getByBasketId(string $basketId): InPostPayOrderInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults;

    /**
     * @param InPostPayOrderInterface $inPostPayOrder
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(InPostPayOrderInterface $inPostPayOrder): bool;

    /**
     * @param int $inPostPayOrderId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $inPostPayOrderId): bool;
}
