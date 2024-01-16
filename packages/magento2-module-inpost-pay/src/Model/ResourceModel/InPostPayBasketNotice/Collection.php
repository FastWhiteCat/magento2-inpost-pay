<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel\InPostPayBasketNotice;

use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use InPost\InPostPay\Model\ResourceModel\InPostPayBasketNotice as InPostPayBasketNoticeResource;
use InPost\InPostPay\Model\InPostPayBasketNotice;

class Collection extends AbstractCollection
{
    protected $_eventPrefix = InPostPayBasketNoticeInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayBasketNoticeInterface::ENTITY_NAME;
    protected $_idFieldName = InPostPayBasketNoticeInterface::BASKET_NOTICE_ID;

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(InPostPayBasketNotice::class, InPostPayBasketNoticeResource::class);
    }
}
