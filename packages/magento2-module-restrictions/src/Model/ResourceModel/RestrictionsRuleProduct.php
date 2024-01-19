<?php

declare(strict_types=1);

namespace InPost\Restrictions\Model\ResourceModel;

use InPost\Restrictions\Api\Data\RestrictionsRuleProductInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RestrictionsRuleProduct extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(
            RestrictionsRuleProductInterface::ENTITY_NAME,
            RestrictionsRuleProductInterface::INDEX_ID
        );
    }
}
