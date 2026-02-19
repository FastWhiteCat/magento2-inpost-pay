<?php
declare(strict_types=1);

namespace InPost\InPostPay\Api;

use InPost\InPostPay\Exception\InPostPayAuthorizationException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;

interface InPostPayQuoteRepositoryInterface
{
    /**
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @return InPostPayQuoteInterface
     * @throws CouldNotSaveException
     */
    public function save(InPostPayQuoteInterface $inPostPayQuote): InPostPayQuoteInterface;

    /**
     * @param int $id
     * @return InPostPayQuoteInterface
     * @throws NoSuchEntityException
     */
    public function get(int $id): InPostPayQuoteInterface;

    /**
     * @param int $quoteId
     * @return InPostPayQuoteInterface
     * @throws NoSuchEntityException
     */
    public function getByQuoteId(int $quoteId): InPostPayQuoteInterface;

    /**
     * @param string $basketBindingApiKey
     * @return InPostPayQuoteInterface
     * @throws NoSuchEntityException
     */
    public function getByBasketBindingApiKey(string $basketBindingApiKey): InPostPayQuoteInterface;

    /**
     * @param string $basketId
     * @return InPostPayQuoteInterface
     * @throws NoSuchEntityException
     */
    public function getByBasketId(string $basketId): InPostPayQuoteInterface;

    /**
     * @param string $inPostBasketId
     * @return InPostPayQuoteInterface
     * @throws NoSuchEntityException
     */
    public function getByInPostBasketId(string $inPostBasketId): InPostPayQuoteInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults;

    /**
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @return bool true on success
     * @throws CouldNotDeleteException
     * @throws InPostPayAuthorizationException
     */
    public function delete(InPostPayQuoteInterface $inPostPayQuote): bool;

    /**
     * @param int $id
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $id): bool;

    /**
     * @param string $basketId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteByBasketId(string $basketId): bool;
}
