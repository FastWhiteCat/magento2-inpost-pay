<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\ResourceModel;

use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InPostPayBasketNotice extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(
            InPostPayBasketNoticeInterface::ENTITY_NAME,
            InPostPayBasketNoticeInterface::BASKET_NOTICE_ID
        );
    }

    public function getBasketNoticesByInPostPayQuoteId(int $inPostPayQuoteId): array
    {
        $connection = $this->getConnection();

        if (!$connection) {
            throw new LocalizedException(__('Connection is not defined'));
        }

        $mainTable = $this->getMainTable();

        $select = $connection->select()
            ->from(['main_table' => $mainTable], ['basket_notice_id', 'type', 'description'])
            ->where('main_table.inpost_pay_quote_id' . '=?', $inPostPayQuoteId)
            ->where('main_table.is_sent = 0')
            ->order('type desc');

        return $connection->fetchAll($select);
    }

    public function setNoticeAsSent(array $noticeIds, int $inPostPayQuoteId): void
    {
        $connection = $this->getConnection();

        if (!$connection) {
            throw new LocalizedException(__('Connection is not defined'));
        }

        $mainTable = $this->getMainTable();

        $connection->update(
            $mainTable,
            ['is_sent' => true],
            [
                'inpost_pay_quote_id = ?' => $inPostPayQuoteId,
                'basket_notice_id IN (?)' => $noticeIds
            ]
        );
    }
}
