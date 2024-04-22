<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel;

use InPost\InPostPay\Api\Data\InPostPayAvailablePaymentMethodInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InPostPayAvailablePaymentMethod extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(
            InPostPayAvailablePaymentMethodInterface::ENTITY_NAME,
            InPostPayAvailablePaymentMethodInterface::PAYMENT_METHOD_ID
        );
    }

    public function getAllValuesAsArray(): array
    {
        $connection = $this->getConnection();

        if (!$connection) {
            throw new LocalizedException(__('Connection is not defined'));
        }
        $mainTable = $this->getMainTable();

        $select = $connection->select()
            ->from(['main_table' => $mainTable]);

        return $connection->fetchAll($select);
    }

    public function deleteAll(): void
    {
        $connection = $this->getConnection();

        if (!$connection) {
            throw new LocalizedException(__('Connection is not defined'));
        }

        $mainTable = $this->getMainTable();
        $connection->truncateTable($mainTable);
    }

    public function insertMultiple(array $data): void
    {
        $connection = $this->getConnection();

        if (!$connection) {
            throw new LocalizedException(__('Connection is not defined'));
        }

        $mainTable = $this->getMainTable();
        $connection->insertArray($mainTable, [InPostPayAvailablePaymentMethodInterface::PAYMENT_CODE], $data);
    }
}
