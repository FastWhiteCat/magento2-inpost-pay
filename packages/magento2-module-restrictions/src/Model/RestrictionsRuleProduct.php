<?php

declare(strict_types=1);

namespace InPost\Restrictions\Model;

use InPost\Restrictions\Api\Data\RestrictionsRuleProductInterface;
use Magento\Framework\Model\AbstractModel;

class RestrictionsRuleProduct extends AbstractModel implements RestrictionsRuleProductInterface
{
    protected $_eventPrefix = RestrictionsRuleProductInterface::ENTITY_NAME;
    protected $_eventObject = RestrictionsRuleProductInterface::ENTITY_NAME;

    public function _construct(): void
    {
        $this->_init(ResourceModel\RestrictionsRuleProduct::class);
    }

    public function getIndexId(): ?int
    {
        return is_scalar($this->getData(self::INDEX_ID)) ? (int) $this->getData(self::INDEX_ID) : null;
    }

    public function setIndexId(int $indexId): RestrictionsRuleProductInterface
    {
        return $this->setData(self::INDEX_ID, $indexId);
    }

    public function getRuleId(): int
    {
        return is_scalar($this->getData(self::RULE_ID)) ? (int)$this->getData(self::RULE_ID) : 0;
    }

    public function setRuleId(int $ruleId): RestrictionsRuleProductInterface
    {
        return $this->setData(self::RULE_ID, $ruleId);
    }

    public function getProductId(): int
    {
        return is_scalar($this->getData(self::PRODUCT_ID)) ? (int)$this->getData(self::PRODUCT_ID) : 0;
    }

    public function setProductId(int $productId): RestrictionsRuleProductInterface
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    public function getAppliesTo(): int
    {
        return is_scalar($this->getData(self::APPLIES_TO)) ? (int)$this->getData(self::APPLIES_TO) : 0;
    }

    public function setAppliesTo(int $appliesTo): RestrictionsRuleProductInterface
    {
        return $this->setData(self::APPLIES_TO, $appliesTo);
    }
}
