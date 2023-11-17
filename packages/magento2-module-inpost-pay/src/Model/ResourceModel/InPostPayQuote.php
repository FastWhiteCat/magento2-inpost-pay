<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InPostPayQuote extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(InPostPayQuoteInterface::ENTITY_NAME, InPostPayQuoteInterface::INPOST_PAY_QUOTE_ID);
    }
}
