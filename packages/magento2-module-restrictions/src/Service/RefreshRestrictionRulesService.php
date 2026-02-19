<?php

declare(strict_types=1);

namespace InPost\Restrictions\Service;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Model\RestrictionsRuleRepository;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Throwable;

class RefreshRestrictionRulesService
{
    private const RULES_TYPES_TO_REFRESH = [
        0,
        RestrictionsRuleInterface::APPLIES_TO_COURIER,
        RestrictionsRuleInterface::APPLIES_TO_APM,
        RestrictionsRuleInterface::APPLIES_TO_BOTH,
    ];

    public function __construct(
        private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        private readonly RestrictionsRuleRepository $restrictionsRuleRepository,
        private readonly ReloadRestrictionRulesProducts $reloadRestrictionRulesProducts,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void
    {
        try {
            $websiteIds = [];

            foreach ($this->getEnabledRestrictionRules() as $restrictionsRule) {
                $websiteIds[] = $restrictionsRule->getWebsiteId();
                $this->reloadRestrictionRulesProducts->execute($restrictionsRule);
            }

            if (!empty($websiteIds)) {
                $websiteIds = array_unique($websiteIds);

                foreach ($websiteIds as $websiteId) {
                    foreach (self::RULES_TYPES_TO_REFRESH as $appliesTo) {
                        $this->reloadRestrictionRulesProducts->warmUpSystemCacheForRestrictionsList(
                            $websiteId,
                            $appliesTo
                        );
                    }
                }
            }
        } catch (Throwable $e) {
            $errorMsg = __('Could not refresh restricted products. Reason: %1', $e->getMessage());
            $this->logger->error($errorMsg->getText());

            throw new LocalizedException(__($errorMsg));
        }
    }

    /**
     * @return RestrictionsRuleInterface[]
     */
    private function getEnabledRestrictionRules(): array
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->addFilter(RestrictionsRuleInterface::IS_ENABLED, 1)->create();
        $restrictionsRules = [];

        foreach ($this->restrictionsRuleRepository->getList($searchCriteria)->getItems() as $restrictionsRule) {
            if ($restrictionsRule instanceof RestrictionsRuleInterface) {
                $restrictionsRules[] = $restrictionsRule;
            }
        }

        return $restrictionsRules;
    }
}
