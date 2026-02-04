<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel\InPostPayBestsellerProduct;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use InPost\InPostPay\Model\ResourceModel\InPostPayBestsellerProduct as InPostPayBestsellerProductResource;
use InPost\InPostPay\Model\InPostPayBestsellerProduct;

class Collection extends AbstractCollection
{
    protected $_eventPrefix = InPostPayBestsellerProductInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayBestsellerProductInterface::ENTITY_NAME;
    protected $_idFieldName = InPostPayBestsellerProductInterface::BESTSELLER_PRODUCT_ID;

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(InPostPayBestsellerProduct::class, InPostPayBestsellerProductResource::class);
    }
}
