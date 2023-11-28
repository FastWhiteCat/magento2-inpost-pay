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
     * @param string $basketId
     * @return InPostPayQuoteInterface
     * @throws LocalizedException
     */
    public function get(string $basketId): InPostPayQuoteInterface;

    /**
     * @param int $quoteId
     * @return InPostPayQuoteInterface
     * @throws LocalizedException
     */
    public function getByQuoteId(int $quoteId): InPostPayQuoteInterface;

    /**
     * @param string $inPostBasketId
     * @return InPostPayQuoteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * @param string $basketId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(string $basketId): bool;
}
