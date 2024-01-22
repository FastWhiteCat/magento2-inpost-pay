<?php

declare(strict_types=1);

namespace InPost\Restrictions\Service;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use Magento\Framework\Exception\LocalizedException;
use InPost\Restrictions\Api\RestrictionsRuleRepositoryInterface;
use InPost\Restrictions\Api\Data\RestrictionsRuleInterfaceFactory;

class RestrictionsRulePersistorService
{
    /**
     * @param RestrictionsRuleRepositoryInterface $restrictionRuleRepository
     * @param RestrictionsRuleInterfaceFactory $restrictionsRuleFactory
     */
    public function __construct(
        private readonly RestrictionsRuleRepositoryInterface $restrictionRuleRepository,
        private readonly RestrictionsRuleInterfaceFactory $restrictionsRuleFactory
    ) {
    }

    /**
     * @param array $ruleData
     * @return void
     * @throws LocalizedException
     */
    public function execute(array $ruleData): void
    {
        if (isset($ruleData[RestrictionsRuleInterface::RULE_ID])) {
            $restrictionsRule = $this->restrictionRuleRepository->get(
                (int)$ruleData[RestrictionsRuleInterface::RULE_ID]
            );
        } else {
            $restrictionsRule = $this->restrictionsRuleFactory->create();
        }

        $name = (string) ($ruleData[RestrictionsRuleInterface::NAME] ?? '');
        $websiteId = (int) ($ruleData[RestrictionsRuleInterface::WEBSITE_ID] ?? 0);
        $isEnabled = (bool) ($ruleData[RestrictionsRuleInterface::IS_ENABLED] ?? false);
        $appliesTo = (int) ($ruleData[RestrictionsRuleInterface::APPLIES_TO] ?? 0);

        $restrictionsRule->setName($name);
        $restrictionsRule->setWebsiteId($websiteId);
        $restrictionsRule->setIsEnabled($isEnabled);
        $restrictionsRule->setAppliesTo($appliesTo);
        // @phpstan-ignore-next-line
        $restrictionsRule->loadPost($this->formatConditionRules($ruleData));

        $this->restrictionRuleRepository->save($restrictionsRule);
    }

    /**
     * Format condition rules from rendered form POST into format acceptable by abstract rule loadPost method
     * @param array $ruleData
     * @return array
     */
    private function formatConditionRules(array $ruleData): array
    {
        if (isset($ruleData['rule'])) {
            if (isset($ruleData['rule']['conditions'])) {
                $ruleData['conditions'] = $ruleData['rule']['conditions'];
            }
            unset($ruleData['rule']);
        }

        return $ruleData;
    }
}
