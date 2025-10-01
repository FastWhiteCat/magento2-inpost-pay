<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementStoreInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InPostPayCheckoutAgreementStore extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(
            InPostPayCheckoutAgreementStoreInterface::TABLE_NAME,
            InPostPayCheckoutAgreementStoreInterface::ENTITY_ID
        );
    }
}
