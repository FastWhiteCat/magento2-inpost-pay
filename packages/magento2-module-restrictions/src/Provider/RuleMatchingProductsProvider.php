<?php

declare(strict_types=1);

namespace InPost\Restrictions\Provider;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Api\Data\RestrictionsRuleProductInterface;
use InPost\Restrictions\Model\ResourceModel\RestrictionsRuleProduct\Collection as RestrictionsRuleProductCollection;
use InPost\Restrictions\Model\ResourceModel\RestrictionsRuleProduct\CollectionFactory as RuleProductCollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\State;

class RuleMatchingProductsProvider
{
    private const BATCH_SIZE = 1000;

    /**
     * @var int[]
     */
    protected array $productIds = [];

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param RuleProductCollectionFactory $ruleProductCollectionFactory
     * @param Emulation $storeEmulator
     * @param State $state
     */
    public function __construct(
        private readonly ProductCollectionFactory $productCollectionFactory,
        private readonly RuleProductCollectionFactory $ruleProductCollectionFactory,
        private readonly Emulation $storeEmulator,
        private readonly State $state
    ) {
    }

    /**
     * @param RestrictionsRuleInterface $rule
     * @return int[]
     */
    public function getUsingIndex(RestrictionsRuleInterface $rule): array
    {
        if (!$rule->getIsEnabled()) {
            return [];
        }

        /** @var RestrictionsRuleProductCollection $collection */
        $collection = $this->ruleProductCollectionFactory->create();
        $collection->addFieldToFilter(
            RestrictionsRuleProductInterface::RULE_ID,
            ['eq' => (int)$rule->getRuleId()]
        );

        $ruleProductIds = [];
        /** @var RestrictionsRuleProductInterface $ruleProduct */
        foreach ($collection->getItems() as $ruleProduct) {
            $ruleProductIds[] = $ruleProduct->getProductId();
        }

        return $ruleProductIds;
    }

    /**
     * Returns product IDs matching rule conditions
     *
     * @returns int[]
     */
    public function getUsingConditions(RestrictionsRuleInterface $rule, int $storeId): array
    {
        $emulationRequired = $this->isEmulationRequired();
        if ($emulationRequired) {
            $this->storeEmulator->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        }

        $this->productIds = [];
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addStoreFilter($storeId);
        // @phpstan-ignore-next-line
        $rule->getConditions()->collectValidatedAttributes($productCollection);

        /** @var Product $product **/
        foreach ($this->getProducts($productCollection) as $product) {
            $product->setStoreId($storeId);
            if ($rule->getConditions()->validate($product)) {
                $this->productIds[] = (int)$product->getId();
            }
        }

        $this->storeEmulator->stopEnvironmentEmulation();

        return $this->productIds;
    }

    /**
     * Returns batched products collection
     * @param ProductCollection $collection
     * @return iterable
     */
    private function getProducts(ProductCollection $collection): iterable
    {
        $collection->setPageSize(self::BATCH_SIZE);
        $lastPageNumber = $collection->getLastPageNumber();

        for ($pageNumber = 1; $pageNumber <= $lastPageNumber; ++$pageNumber) {
            $batchCollection = clone $collection;

            yield from $batchCollection->setCurPage($pageNumber);
        }
    }

    /**
     * Checks if area code is set and is frontend
     * @return bool
     */
    private function isEmulationRequired(): bool
    {
        try {
            $area = $this->state->getAreaCode();

            return $area === Area::AREA_FRONTEND;
        } catch (LocalizedException $e) {
            return false;
        }
    }
}
