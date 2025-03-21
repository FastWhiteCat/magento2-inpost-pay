<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterfaceFactory;
use Magento\Framework\Api\SearchResultsFactory;
use InPost\InPostPay\Api\InPostPayCheckoutAgreementRepositoryInterface;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreement as InPostPayCheckoutAgreementResource;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreement\CollectionFactory
    as InPostPayCheckoutAgreementCollectionFactory;

class InPostPayCheckoutAgreementRepository implements InPostPayCheckoutAgreementRepositoryInterface
{
    /**
     * @param InPostPayCheckoutAgreementResource $resource
     * @param InPostPayCheckoutAgreementInterfaceFactory $inPostPayCheckoutAgreementInterfaceFactory
     * @param InPostPayCheckoutAgreementCollectionFactory $inPostPayCheckoutAgreementCollectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        private readonly InPostPayCheckoutAgreementResource $resource,
        private readonly InPostPayCheckoutAgreementInterfaceFactory $inPostPayCheckoutAgreementInterfaceFactory,
        private readonly InPostPayCheckoutAgreementCollectionFactory $inPostPayCheckoutAgreementCollectionFactory,
        private readonly SearchResultsFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    /**
     * @param InPostPayCheckoutAgreementInterface $inPostPayCheckoutAgreement
     * @return InPostPayCheckoutAgreementInterface
     * @throws CouldNotSaveException
     */
    public function save(
        InPostPayCheckoutAgreementInterface $inPostPayCheckoutAgreement
    ): InPostPayCheckoutAgreementInterface {
        try {
            // @phpstan-ignore-next-line
            $this->resource->save($inPostPayCheckoutAgreement);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the InPost Pay Checkout Agreement: %1', $exception->getMessage())
            );
        }

        return $inPostPayCheckoutAgreement;
    }

    /**
     * @param int $agreementId
     * @return InPostPayCheckoutAgreementInterface
     * @throws NoSuchEntityException
     */
    public function get(int $agreementId): InPostPayCheckoutAgreementInterface
    {
        /** @var InPostPayCheckoutAgreementInterface $agreement */
        $agreement = $this->inPostPayCheckoutAgreementInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($agreement, $agreementId);

        if (!$agreement->getAgreementId()) {
            throw new NoSuchEntityException(
                __('InPost Pay Checkout Agreement with id "%1" does not exist.', $agreementId)
            );
        }
        return $agreement;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults
    {
        $collection = $this->inPostPayCheckoutAgreementCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $items = [];

        foreach ($collection as $model) {
            $items[] = $model;
        }

        // @phpstan-ignore-next-line
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    public function delete(InPostPayCheckoutAgreementInterface $inPostPayCheckoutAgreement): bool
    {
        try {
            // @phpstan-ignore-next-line
            $this->resource->delete($inPostPayCheckoutAgreement);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the InPost Pay Checkout Agreement: %1', $exception->getMessage())
            );
        }

        return true;
    }

    /**
     * @param int $agreementId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $agreementId): bool
    {
        return $this->delete($this->get($agreementId));
    }
}
