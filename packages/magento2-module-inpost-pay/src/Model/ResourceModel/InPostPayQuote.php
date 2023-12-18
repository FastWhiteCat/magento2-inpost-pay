<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Enum\InPostBasketStatus;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InPostPayQuote extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(InPostPayQuoteInterface::ENTITY_NAME, InPostPayQuoteInterface::INPOST_PAY_QUOTE_ID);
    }

    public function isRefreshRequired(string $basketId): bool
    {
        $connection = $this->getConnection();
        $mainTable = $this->getMainTable();

        $select = $connection->select()
            ->from($mainTable, ['refresh_required'])
            ->where('basket_id' . '=?', $basketId);

        return (bool)$connection->fetchOne($select);
    }

    public function isBasketConnected(int $quoteId): bool
    {
        $connection = $this->getConnection();
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
        $mainTable = $this->getMainTable();

        $connection->update(
            $mainTable,
            ['refresh_required' => $refreshRequired],
            ['basket_id = ?' => $basketId]
        );
    }
}
