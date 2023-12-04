<?php
declare(strict_types=1);

namespace InPost\InPostPay\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;

interface InPostPayQuoteRepositoryInterface
{
    /**
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @return InPostPayQuoteInterface
     * @throws LocalizedException
     */
    public function save(InPostPayQuoteInterface $inPostPayQuote): InPostPayQuoteInterface;

    /**
     * @param int $inPostPayQuoteId
     * @return InPostPayQuoteInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function get(int $inPostPayQuoteId): InPostPayQuoteInterface;

    /**
     * @param int $quoteId
     * @return InPostPayQuoteInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getByQuoteId(int $quoteId): InPostPayQuoteInterface;

    /**
     * @param string $basketId
     * @return InPostPayQuoteInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getByBasketId(string $basketId): InPostPayQuoteInterface;

    /**
     * @param string $inPostBasketId
     * @return InPostPayQuoteInterface
     * @throws LocalizedException
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
     * @throws LocalizedException
     */
    public function delete(InPostPayQuoteInterface $inPostPayQuote): bool;

    /**
     * @param int $inPostPayQuoteId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $inPostPayQuoteId): bool;
}
