<?php

declare(strict_types=1);

namespace InPost\Restrictions\Model\ResourceModel\RestrictionsRuleProduct;

use InPost\Restrictions\Api\Data\RestrictionsRuleProductInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use InPost\Restrictions\Model\ResourceModel\RestrictionsRuleProduct as RestrictionsRuleProductResource;
use InPost\Restrictions\Model\RestrictionsRuleProduct;

class Collection extends AbstractCollection
{
    protected $_eventPrefix = RestrictionsRuleProductInterface::ENTITY_NAME;
    protected $_eventObject = RestrictionsRuleProductInterface::ENTITY_NAME;
    protected $_idFieldName = RestrictionsRuleProductInterface::INDEX_ID;

    protected function _construct(): void
    {
        $this->_init(
            RestrictionsRuleProduct::class,
            RestrictionsRuleProductResource::class,
        );
    }
}
