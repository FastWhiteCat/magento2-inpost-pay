<?php

declare(strict_types=1);

namespace InPost\Restrictions\Observer;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Provider\RestrictedProductIdsProvider;
use InPost\Restrictions\Service\ReloadRestrictionRulesProducts;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class GenerateRestrictedProductsListObserver implements ObserverInterface
{
    public function __construct(private readonly ReloadRestrictionRulesProducts $reloadRestrictionRulesProducts)
    {
    }

    public function execute(Observer $observer): void
    {
        $restrictionsRule = $observer->getEvent()->getData(RestrictionsRuleInterface::ENTITY_NAME);
        if ($restrictionsRule instanceof RestrictionsRuleInterface) {
            $websiteId = $restrictionsRule->getWebsiteId();
            $appliesTo = $restrictionsRule->getAppliesTo();
            $this->reloadRestrictionRulesProducts->execute($restrictionsRule);
            $this->reloadRestrictionRulesProducts->warmUpSystemCacheForRestrictionsList($websiteId, $appliesTo);
        }
    }
}
