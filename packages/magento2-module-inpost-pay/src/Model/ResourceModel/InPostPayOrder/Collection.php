<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel\InPostPayOrder;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use InPost\InPostPay\Model\ResourceModel\InPostPayOrder as InPostPayOrderResource;
use InPost\InPostPay\Model\InPostPayOrder;

class Collection extends AbstractCollection
{
    protected $_eventPrefix = InPostPayOrderInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayOrderInterface::ENTITY_NAME;
    protected $_idFieldName = InPostPayOrderInterface::INPOST_PAY_ORDER_ID;

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(InPostPayOrder::class, InPostPayOrderResource::class);
    }
}
