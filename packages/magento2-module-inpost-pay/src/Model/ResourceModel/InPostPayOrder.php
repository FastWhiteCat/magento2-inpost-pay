<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InPostPayOrder extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(InPostPayOrderInterface::ENTITY_NAME, InPostPayOrderInterface::INPOST_PAY_ORDER_ID);
    }

    public function isOrderExistForBasketId(string $basketId): bool
    {
        $connection = $this->getConnection();
        $mainTable = $this->getMainTable();

        $select = $connection->select()
            ->from($mainTable, ['order_id'])
            ->where('basket_id' . '=?', $basketId);

        return (bool)$connection->fetchOne($select);
    }
}
