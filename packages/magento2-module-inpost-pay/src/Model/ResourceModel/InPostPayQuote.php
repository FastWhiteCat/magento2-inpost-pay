<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Enum\InPostBasketStatus;
use InPost\InPostPay\Exception\BasketNotFoundException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InPostPayQuote extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(InPostPayQuoteInterface::ENTITY_NAME, InPostPayQuoteInterface::INPOST_PAY_QUOTE_ID);
    }

    /**
     * @throws LocalizedException
     * @throws BasketNotFoundException
     */
    public function getCartVersionAndOrderId(string $basketId): array
    {
        $connection = $this->getConnection();

        if (!$connection) {
            throw new LocalizedException(__('Connection is not defined'));
        }

        $mainTable = $this->getMainTable();
        $inpostOrderTable = $this->getTable('inpost_pay_order');

        $select = $connection->select()
            ->from(['main_table' => $mainTable], [InPostPayQuoteInterface::CART_VERSION])
            ->joinLeft(['io' => $inpostOrderTable], 'io.basket_id = main_table.basket_id', 'order_id')
            ->where('main_table.basket_id' . '=?', $basketId);

        $result = $connection->fetchRow($select);

        if (empty($result)) {
            $inPostPayQuoteId = $this->getInPostPayQuoteIdByBasketId($basketId);
            if (empty($inPostPayQuoteId)) {
                throw new BasketNotFoundException(__('Could not find a basket with ID:%1', $basketId));
            }
        }

        return is_array($result) ? $result : [];
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

    public function updateCartVersion(string $basketId): void
    {
        $connection = $this->getConnection();

        if (!$connection) {
            throw new LocalizedException(__('Connection is not defined'));
        }

        $connection->update(
            $this->getMainTable(),
            [InPostPayQuoteInterface::CART_VERSION => uniqid()],
            [sprintf('%s = ?', InPostPayQuoteInterface::BASKET_ID) => $basketId]
        );
    }

    public function getInPostPayQuoteIdByBasketId(string $basketId): int
    {
        $connection = $this->getConnection();

        if (!$connection) {
            throw new LocalizedException(__('Connection is not defined'));
        }

        $mainTable = $this->getMainTable();

        $select = $connection->select()
            ->from($mainTable, ['inpost_pay_quote_id'])
            ->where('basket_id' . '=?', $basketId);

        return (int)$connection->fetchOne($select);
    }
}
