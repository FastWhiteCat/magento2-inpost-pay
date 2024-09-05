<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel\InPostPayAvailablePaymentMethod;

use InPost\InPostPay\Api\Data\InPostPayAvailablePaymentMethodInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use InPost\InPostPay\Model\ResourceModel\InPostPayAvailablePaymentMethod as InPostPayAvailablePaymentMethodResource;
use InPost\InPostPay\Model\InPostPayAvailablePaymentMethod;

class Collection extends AbstractCollection
{
    protected $_eventPrefix = InPostPayAvailablePaymentMethodInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayAvailablePaymentMethodInterface::ENTITY_NAME;
    protected $_idFieldName = InPostPayAvailablePaymentMethodInterface::PAYMENT_METHOD_ID;

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(InPostPayAvailablePaymentMethod::class, InPostPayAvailablePaymentMethodResource::class);
    }
}
