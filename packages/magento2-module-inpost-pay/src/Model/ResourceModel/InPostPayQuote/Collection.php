<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel\InPostPayQuote;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use InPost\InPostPay\Model\ResourceModel\InPostPayQuote as InPostPayQuoteResource;
use InPost\InPostPay\Model\InPostPayQuote;

class Collection extends AbstractCollection
{
    protected $_eventPrefix = InPostPayQuoteInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayQuoteInterface::ENTITY_NAME;
    protected $_idFieldName = InPostPayQuoteInterface::INPOST_PAY_QUOTE_ID;

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(InPostPayQuote::class, InPostPayQuoteResource::class);
    }
}
