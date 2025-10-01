<?php

declare(strict_types=1);

namespace InPost\Restrictions\Api\Data;

use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Action\Collection as ActionCollection;

interface RestrictionsRuleInterface
{
    public const TABLE_NAME = 'inpost_restrictions_rule';
    public const ENTITY_NAME = 'inpost_restrictions_rule';
    public const RULE_ID = 'rule_id';
    public const NAME = 'name';
    public const IS_ENABLED = 'is_enabled';
    public const WEBSITE_ID = 'website_id';
    public const APPLIES_TO = 'applies_to';
    public const CONDITIONS_SERIALIZED = 'conditions_serialized';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const APPLIES_TO_COURIER = 1;
    public const APPLIES_TO_APM = 2;
    public const APPLIES_TO_BOTH = 3;
    public const APPLIES_TO_DIGITAL = 4;

    public function getRuleId(): ?int;
    public function setRuleId(int $ruleId): RestrictionsRuleInterface;
    public function getName(): string;
    public function setName(string $name): RestrictionsRuleInterface;
    public function getIsEnabled(): bool;
    public function setIsEnabled(bool $isEnabled): RestrictionsRuleInterface;
    public function getWebsiteId(): int;
    public function setWebsiteId(int $websiteId): RestrictionsRuleInterface;
    public function getAppliesTo(): int;
    public function setAppliesTo(int $appliesTo): RestrictionsRuleInterface;
    public function getConditionsSerialized(): ?string;
    public function setConditionsSerialized(string $conditionsSerialized): RestrictionsRuleInterface;
    public function getCreatedAt(): string;
    public function getUpdatedAt(): string;

    /**
     * @return Combine
     */
    public function getConditions();
    public function getConditionsInstance(): Combine;
    public function getActionsInstance(): ActionCollection;
    public function getConditionsFieldSetId(string $formName = ''): string;

    /**
     * Get product IDs that meet requirements in configured conditions
     * @return array
     */
    public function getProductIdsByConditions(): array;

    /**
     * Get indexed product IDs that meet requirements in configured conditions
     * @return array
     */
    public function getRuleProductIds(): array;
}
