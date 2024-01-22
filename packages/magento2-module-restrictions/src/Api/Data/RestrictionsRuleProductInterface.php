<?php

declare(strict_types=1);

namespace InPost\Restrictions\Api\Data;

interface RestrictionsRuleProductInterface
{
    public const TABLE_NAME = 'inpost_restrictions_rule_product';
    public const ENTITY_NAME = 'inpost_restrictions_rule_product';
    public const INDEX_ID = 'index_id';
    public const RULE_ID = 'rule_id';
    public const WEBSITE_ID = 'website_id';
    public const PRODUCT_ID = 'product_id';
    public const APPLIES_TO = 'applies_to';

    public function getIndexId(): ?int;
    public function setIndexId(int $indexId): RestrictionsRuleProductInterface;
    public function getRuleId(): int;
    public function setRuleId(int $ruleId): RestrictionsRuleProductInterface;
    public function getProductId(): int;
    public function setProductId(int $productId): RestrictionsRuleProductInterface;
    public function getAppliesTo(): int;
    public function setAppliesTo(int $appliesTo): RestrictionsRuleProductInterface;
}
