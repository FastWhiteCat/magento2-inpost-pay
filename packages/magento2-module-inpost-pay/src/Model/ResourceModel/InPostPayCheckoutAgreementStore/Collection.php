<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreementStore;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementStoreInterface;
use InPost\InPostPay\Model\InPostPayCheckoutAgreementStore;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreementStore as InPostPayCheckoutAgreementStoreResource;

class Collection extends AbstractCollection
{
    protected $_eventPrefix = InPostPayCheckoutAgreementStoreInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayCheckoutAgreementStoreInterface::ENTITY_NAME;
    protected $_idFieldName = InPostPayCheckoutAgreementStoreInterface::ENTITY_ID;

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(InPostPayCheckoutAgreementStore::class, InPostPayCheckoutAgreementStoreResource::class);
    }
}
