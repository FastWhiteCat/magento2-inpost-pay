<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel;

use InPost\InPostPay\Api\Data\RefundTransactionInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RefundTransaction extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(RefundTransactionInterface::ENTITY_NAME, RefundTransactionInterface::MAPPING_ID);
    }
}
