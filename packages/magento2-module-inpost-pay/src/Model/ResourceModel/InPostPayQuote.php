<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Enum\InPostBasketStatus;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InPostPayQuote extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(InPostPayQuoteInterface::ENTITY_NAME, InPostPayQuoteInterface::INPOST_PAY_QUOTE_ID);
    }

    public function getRefreshRequiredAndOrderId(string $basketId): array
    {
        $connection = $this->getConnection();

        if (!$connection) {
            throw new LocalizedException(__('Connection is not defined'));
        }

        $mainTable = $this->getMainTable();
        $inpostOrderTable = $this->getTable('inpost_pay_order');

        $select = $connection->select()
            ->from(['main_table' => $mainTable], ['refresh_required'])
            ->joinLeft(['io' => $inpostOrderTable], 'io.basket_id = main_table.basket_id', 'order_id')
            ->where('main_table.basket_id' . '=?', $basketId);

        return $connection->fetchRow($select);
    }

    public function isBasketConnected(int $quoteId): bool
    {
        $connection = $this->getConnection();

        if (!$connection) {
            throw new LocalizedException(__('Connection is not defined'));
        }

        $mainTable = $this->getMainTable();

        $select = $connection->select()
            ->from($mainTable, ['basket_id'])
            ->where('quote_id' . '=?', $quoteId)
            ->where('status' . '=?', InPostBasketStatus::SUCCESS->value);

        return (bool)$connection->fetchOne($select);
    }

    public function updateRefreshRequired(string $basketId, bool $refreshRequired = false): void
    {
        $connection = $this->getConnection();

        if (!$connection) {
            throw new LocalizedException(__('Connection is not defined'));
        }

        $mainTable = $this->getMainTable();

        $connection->update(
            $mainTable,
            ['refresh_required' => $refreshRequired],
            ['basket_id = ?' => $basketId]
        );
    }
}
