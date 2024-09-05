<?php

declare(strict_types=1);

namespace InPost\Restrictions\Provider;

use InPost\Restrictions\Model\Cache\RestrictedProducts\Type as RestrictedProductsCache;
use InPost\Restrictions\Service\RestrictionsRuleProductMassActionService;
use Magento\Framework\Serialize\SerializerInterface;

class RestrictedProductIdsProvider
{
    private ?array $cachedRestrictedProductIds = null;

    public function __construct(
        private readonly RestrictedProductsCache $restrictedProductsCache,
        private readonly RestrictionsRuleProductMassActionService $restrictionRuleProductMassActionService,
        private readonly SerializerInterface $serializer
    ) {
    }

    public function getList(int $websiteId, int $appliesTo = 0): array
    {
        $cacheKey = $this->getCacheKey($websiteId, $appliesTo);
        if (isset($this->cachedRestrictedProductIds[$cacheKey])
            && is_array($this->cachedRestrictedProductIds[$cacheKey])
        ) {
            return $this->cachedRestrictedProductIds[$cacheKey];
        }

        $encodedRestrictedProductsList = (string)$this->restrictedProductsCache->load($cacheKey);
        if (empty($encodedRestrictedProductsList)) {
            if ($appliesTo !== 0) {
                $encodedRestrictedProductsList = (string)$this->serializer->serialize(
                    $this->restrictionRuleProductMassActionService->getRuleProductsByAppliesTo($websiteId, $appliesTo)
                );
            } else {
                $encodedRestrictedProductsList = (string)$this->serializer->serialize(
                    $this->restrictionRuleProductMassActionService->getRuleProductsByWebsiteId($websiteId)
                );
            }

            $this->restrictedProductsCache->save(
                $encodedRestrictedProductsList,
                $cacheKey,
                [RestrictedProductsCache::CACHE_TAG],
                RestrictedProductsCache::TTL
            );
        }

        $this->cachedRestrictedProductIds[$cacheKey] = (array)$this->serializer->unserialize(
            $encodedRestrictedProductsList
        );

        return $this->cachedRestrictedProductIds[$cacheKey];
    }

    public function flushList(int $websiteId, int $appliesTo): void
    {
        $cacheKey = $this->getCacheKey($websiteId, $appliesTo);
        $this->restrictedProductsCache->clean();
        $this->restrictedProductsCache->remove($cacheKey);
        $this->cachedRestrictedProductIds[$cacheKey] = null;
    }

    private function getCacheKey(int $websiteId, int $restrictionType): string
    {
        return sprintf('%s_%s', $websiteId, $restrictionType);
    }
}
