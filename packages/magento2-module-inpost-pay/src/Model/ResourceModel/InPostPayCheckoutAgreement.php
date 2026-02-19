<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InPostPayCheckoutAgreement extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(
            InPostPayCheckoutAgreementInterface::ENTITY_NAME,
            InPostPayCheckoutAgreementInterface::AGREEMENT_ID
        );
    }
}
