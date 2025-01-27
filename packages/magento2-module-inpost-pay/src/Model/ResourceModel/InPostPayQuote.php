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
