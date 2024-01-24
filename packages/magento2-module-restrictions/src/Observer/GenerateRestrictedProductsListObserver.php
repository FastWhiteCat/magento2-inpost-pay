<?php

declare(strict_types=1);

namespace InPost\Restrictions\Observer;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Api\Data\RestrictionsRuleProductInterface;
use InPost\Restrictions\Provider\RestrictedProductIdsProvider;
use InPost\Restrictions\Service\RestrictionsRuleProductMassActionService;
use Magento\InventoryCache\Model\FlushCacheByProductIds;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class GenerateRestrictedProductsListObserver implements ObserverInterface
{
    public function __construct(
        private readonly RestrictionsRuleProductMassActionService $restrictionRuleProductMassActionService,
        private readonly RestrictedProductIdsProvider $restrictedProductIdsProvider,
        private readonly FlushCacheByProductIds $flushCacheByProductIds
    ) {
    }

    public function execute(Observer $observer): void
    {
        $restrictionsRule = $observer->getEvent()->getData(RestrictionsRuleInterface::ENTITY_NAME);
        if ($restrictionsRule instanceof RestrictionsRuleInterface) {
            $ruleId = (int)$restrictionsRule->getRuleId();
            $websiteId = $restrictionsRule->getWebsiteId();
            $appliesTo = $restrictionsRule->getAppliesTo();
            $oldProductIds = $this->restrictionRuleProductMassActionService->getRuleProductsByRuleIds([$ruleId]);
            $this->cleanRuleProductsByRuleId($ruleId);
            if ($restrictionsRule->getIsEnabled()) {
                $this->restrictionRuleProductMassActionService->massCreateRuleProducts(
                    $this->prepareRestrictionsRuleProductsData($restrictionsRule)
                );
            }
            $newProductIds = $this->restrictionRuleProductMassActionService->getRuleProductsByRuleIds([$ruleId]);
            $this->flushCacheForChangedProductIds($oldProductIds, $newProductIds, $websiteId, $appliesTo);
            $this->warmUpSystemCacheForRestrictionsList($websiteId, $appliesTo);
        }
    }

    private function cleanRuleProductsByRuleId(int $restrictionsRuleId): void
    {
        $this->restrictionRuleProductMassActionService->massDeleteRuleProductsByRuleIds([$restrictionsRuleId]);
    }

    private function flushCacheForChangedProductIds(
        array $oldProductIds,
        array $newProductIds,
        int $websiteId,
        int $appliesTo
    ): void {
        $productIds = array_unique(
            array_merge(
                array_diff($oldProductIds, $newProductIds),
                array_diff($newProductIds, $oldProductIds)
            )
        );

        if (!empty($productIds)) {
            $this->restrictedProductIdsProvider->flushList($websiteId, $appliesTo);
            $this->flushCacheByProductIds->execute($productIds);
        }
    }

    private function warmUpSystemCacheForRestrictionsList(int $websiteId, int $appliesTo): void
    {
        $this->restrictedProductIdsProvider->getList($websiteId, $appliesTo);
    }

    private function prepareRestrictionsRuleProductsData(RestrictionsRuleInterface $restrictionsRule): array
    {
        $ruleProductsData = [];
        $productIds = $restrictionsRule->getProductIdsByConditions();
        $ruleId = (int)$restrictionsRule->getRuleId();
        $appliesTo = $restrictionsRule->getAppliesTo();
        $websiteId = $restrictionsRule->getWebsiteId();
        foreach ($productIds as $productId) {
            $ruleProductsData[] = [
                RestrictionsRuleProductInterface::RULE_ID => $ruleId,
                RestrictionsRuleProductInterface::APPLIES_TO => $appliesTo,
                RestrictionsRuleProductInterface::PRODUCT_ID => (int)$productId,
                RestrictionsRuleProductInterface::WEBSITE_ID => $websiteId
            ];
        }

        return $ruleProductsData;
    }
}
