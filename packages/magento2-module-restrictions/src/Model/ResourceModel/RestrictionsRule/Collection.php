<?php

declare(strict_types=1);

namespace InPost\Restrictions\Model\ResourceModel\RestrictionsRule;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use InPost\Restrictions\Model\ResourceModel\RestrictionsRule as RestrictionsRuleResource;
use InPost\Restrictions\Model\RestrictionsRule;

class Collection extends AbstractCollection
{
    protected $_eventPrefix = RestrictionsRuleInterface::ENTITY_NAME;
    protected $_eventObject = RestrictionsRuleInterface::ENTITY_NAME;
    protected $_idFieldName = RestrictionsRuleInterface::RULE_ID;

    protected function _construct(): void
    {
        $this->_init(
            RestrictionsRule::class,
            RestrictionsRuleResource::class
        );
    }
}
