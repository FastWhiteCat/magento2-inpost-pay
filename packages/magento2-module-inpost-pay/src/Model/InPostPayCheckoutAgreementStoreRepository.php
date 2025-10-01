<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use Exception;
use InPost\InPostPay\Api\InPostPayCheckoutAgreementStoreRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementStoreInterface;
use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementStoreInterfaceFactory as AgreementStoreFactory;
use Magento\Framework\Api\SearchResultsFactory;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreementStore as InPostPayCheckoutAgreementStoreResource;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreementStore\CollectionFactory
    as AgreementStoreCollectionFactory;

class InPostPayCheckoutAgreementStoreRepository implements InPostPayCheckoutAgreementStoreRepositoryInterface
{
    /**
     * @param InPostPayCheckoutAgreementStoreResource $resource
     * @param AgreementStoreFactory $inPostPayCheckoutAgreementStoreInterfaceFactory
     * @param AgreementStoreCollectionFactory $inPostPayCheckoutAgreementStoreCollectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        private readonly InPostPayCheckoutAgreementStoreResource $resource,
        private readonly AgreementStoreFactory $inPostPayCheckoutAgreementStoreInterfaceFactory,
        private readonly AgreementStoreCollectionFactory $inPostPayCheckoutAgreementStoreCollectionFactory,
        private readonly SearchResultsFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    /**
     * @param InPostPayCheckoutAgreementStoreInterface $inPostPayCheckoutAgreementStore
     * @return InPostPayCheckoutAgreementStoreInterface
     * @throws CouldNotSaveException
     */
    public function save(
        InPostPayCheckoutAgreementStoreInterface $inPostPayCheckoutAgreementStore
    ): InPostPayCheckoutAgreementStoreInterface {
        try {
            // @phpstan-ignore-next-line
            $this->resource->save($inPostPayCheckoutAgreementStore);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the InPost Pay Checkout Agreement Store: %1', $exception->getMessage())
            );
        }

        return $inPostPayCheckoutAgreementStore;
    }

    /**
     * @param int $entityId
     * @return InPostPayCheckoutAgreementStoreInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): InPostPayCheckoutAgreementStoreInterface
    {
        /** @var InPostPayCheckoutAgreementStoreInterface $agreementStore */
        $agreementStore = $this->inPostPayCheckoutAgreementStoreInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($agreementStore, $entityId);

        if (!$agreementStore->getAgreementId()) {
            throw new NoSuchEntityException(
                __('InPost Pay Checkout Agreement Store with id "%1" does not exist.', $entityId)
            );
        }
        return $agreementStore;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults
    {
        $collection = $this->inPostPayCheckoutAgreementStoreCollectionFactory->create();
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

    public function delete(InPostPayCheckoutAgreementStoreInterface $inPostPayCheckoutAgreementStore): bool
    {
        try {
            // @phpstan-ignore-next-line
            $this->resource->delete($inPostPayCheckoutAgreementStore);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the InPost Pay Checkout Agreement Store: %1', $exception->getMessage())
            );
        }

        return true;
    }

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $entityId): bool
    {
        return $this->delete($this->get($entityId));
    }
}
