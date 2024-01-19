<?php

declare(strict_types=1);

namespace InPost\Restrictions\Model\ResourceModel;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RestrictionsRule extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(RestrictionsRuleInterface::ENTITY_NAME, RestrictionsRuleInterface::RULE_ID);
    }
}
