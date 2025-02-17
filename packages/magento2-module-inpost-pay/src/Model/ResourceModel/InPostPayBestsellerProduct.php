<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InPostPayBestsellerProduct extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(
            InPostPayBestsellerProductInterface::ENTITY_NAME,
            InPostPayBestsellerProductInterface::BESTSELLER_PRODUCT_ID
        );
    }
}
