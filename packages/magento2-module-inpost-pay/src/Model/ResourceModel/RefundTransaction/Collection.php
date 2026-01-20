<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel\RefundTransaction;

use InPost\InPostPay\Api\Data\RefundTransactionInterface;
use InPost\InPostPay\Model\RefundTransaction;
use InPost\InPostPay\Model\ResourceModel\RefundTransaction as RefundTransactionResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_eventPrefix = RefundTransactionInterface::ENTITY_NAME;
    protected $_eventObject = RefundTransactionInterface::ENTITY_NAME;
    protected $_idFieldName = RefundTransactionInterface::MAPPING_ID;

    protected function _construct(): void
    {
        $this->_init(RefundTransaction::class, RefundTransactionResource::class);
    }
}
