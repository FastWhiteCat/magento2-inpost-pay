<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use Exception;
use InPost\InPostPay\Api\InPostPayBestsellerProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterfaceFactory;
use Magento\Framework\Api\SearchResultsFactory;
use InPost\InPostPay\Model\ResourceModel\InPostPayBestsellerProduct as InPostPayBestsellerProductResource;
use InPost\InPostPay\Model\ResourceModel\InPostPayBestsellerProduct\Collection;
use InPost\InPostPay\Model\ResourceModel\InPostPayBestsellerProduct\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InPostPayBestsellerProductRepository implements InPostPayBestsellerProductRepositoryInterface
{
    /**
     * @var InPostPayBestsellerProductInterface[]
     */
    private array $instances = [];

    /**
     * @param CollectionProcessorInterface $collectionProcessor
     * @param InPostPayBestsellerProductResource $resource
     * @param InPostPayBestsellerProductInterfaceFactory $inPostPayBestsellerProductInterfaceFactory
     * @param CollectionFactory $inPostPayBestsellerProductCollectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        private readonly CollectionProcessorInterface $collectionProcessor,
        private readonly InPostPayBestsellerProductResource $resource,
        private readonly InPostPayBestsellerProductInterfaceFactory $inPostPayBestsellerProductInterfaceFactory,
        private readonly CollectionFactory $inPostPayBestsellerProductCollectionFactory,
        private readonly SearchResultsFactory $searchResultsFactory,
        private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
    }

    public function save(
        InPostPayBestsellerProductInterface $inPostPayBestsellerProduct
    ): InPostPayBestsellerProductInterface {
        try {
            // @phpstan-ignore-next-line
            $this->resource->save($inPostPayBestsellerProduct);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not save InPost Pay Bestseller Product: %1', $e->getMessage()));
        }

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $criteria = $searchCriteriaBuilder
            ->addFilter(InPostPayBestsellerProductInterface::SKU, $inPostPayBestsellerProduct->getSku())
            ->addFilter(InPostPayBestsellerProductInterface::WEBSITE_ID, $inPostPayBestsellerProduct->getWebsiteId())
            ->create();

        $bestsellerProducts = $this->getList($criteria)->getItems();
        $inPostPayBestsellerProduct = null;

        if (count($bestsellerProducts)) {
            $inPostPayBestsellerProduct = current($bestsellerProducts);
        }

        if (!$inPostPayBestsellerProduct instanceof InPostPayBestsellerProductInterface) {
            throw new CouldNotSaveException(__('Saving InPost Pay Bestseller Product failed.'));
        }

        $this->instances[$inPostPayBestsellerProduct->getBestsellerProductId()] = $inPostPayBestsellerProduct;

        return $inPostPayBestsellerProduct;
    }

    /**
     * @param int $id
     * @return InPostPayBestsellerProductInterface
     * @throws NoSuchEntityException
     */
    public function get(int $id): InPostPayBestsellerProductInterface
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        /** @var InPostPayBestsellerProductInterface $inPostPayBestsellerProduct */
        $inPostPayBestsellerProduct = $this->inPostPayBestsellerProductInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($inPostPayBestsellerProduct, $id);

        if (empty($inPostPayBestsellerProduct->getBestsellerProductId())) {
            throw new NoSuchEntityException(
                __('InPost Pay Bestseller Product with ID "%1" does not exist.', $id)
            );
        }

        $this->instances[$inPostPayBestsellerProduct->getBestsellerProductId()] = $inPostPayBestsellerProduct;

        return $inPostPayBestsellerProduct;
    }

    /**
     * @param string $sku
     * @param int $websiteId
     * @return InPostPayBestsellerProductInterface
     * @throws NoSuchEntityException
     */
    public function getBySkuAndWebsiteId(string $sku, int $websiteId): InPostPayBestsellerProductInterface
    {
        /** @var Collection $collection */
        $collection = $this->inPostPayBestsellerProductCollectionFactory->create();
        $collection->addFieldToFilter(InPostPayBestsellerProductInterface::WEBSITE_ID, (string)$websiteId);
        $collection->addFieldToFilter(InPostPayBestsellerProductInterface::SKU, $sku);
        $items = $collection->getItems();

        if (!empty($items)) {
            $inPostPayBestsellerProduct = current($items);
        }

        if (!isset($inPostPayBestsellerProduct)
            || !$inPostPayBestsellerProduct instanceof InPostPayBestsellerProductInterface
        ) {
            throw new NoSuchEntityException(
                __(
                    'InPost Pay Bestseller Product with SKU "%1" for Website ID:%2 does not exist.',
                    $sku,
                    $websiteId
                )
            );
        }

        return $inPostPayBestsellerProduct;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults
    {
        $collection = $this->inPostPayBestsellerProductCollectionFactory->create();
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

    /**
     * @param InPostPayBestsellerProductInterface $inPostPayBestsellerProduct
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(InPostPayBestsellerProductInterface $inPostPayBestsellerProduct): bool
    {
        try {
            $bestsellerProductId = $inPostPayBestsellerProduct->getBestsellerProductId();
            // @phpstan-ignore-next-line
            $this->resource->delete($inPostPayBestsellerProduct);
            unset($this->instances[$bestsellerProductId]);
        } catch (Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not delete InPost Pay Bestseller Product: %1', $e->getMessage())
            );
        }

        return true;
    }

    /**
     * @param int $id
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $id): bool
    {
        return $this->delete($this->get($id));
    }

    /**
     * @return void
     */
    public function clearInstancesCache(): void
    {
        $this->instances = [];
    }
}
