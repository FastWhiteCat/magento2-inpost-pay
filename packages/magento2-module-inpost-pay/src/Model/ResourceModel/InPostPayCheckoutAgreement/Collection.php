<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreement;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use InPost\InPostPay\Model\InPostPayCheckoutAgreement;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreement as InPostPayCheckoutAgreementResource;

class Collection extends AbstractCollection
{
    protected $_eventPrefix = InPostPayCheckoutAgreementInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayCheckoutAgreementInterface::ENTITY_NAME;
    protected $_idFieldName = InPostPayCheckoutAgreementInterface::AGREEMENT_ID;

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(InPostPayCheckoutAgreement::class, InPostPayCheckoutAgreementResource::class);
    }
}
