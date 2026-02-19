<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Response;

use Magento\Framework\DataObject;

class TransactionRefundResponse extends DataObject
{
    public const EXTERNAL_REFUND_ID = 'external_refund_id';
    public const REFUND_AMOUNT = 'refund_amount';
    public const STATUS = 'status';
    public const DESCRIPTION = 'description';

    public function getExternalRefundId(): string
    {
        $externalRefundId = $this->getData(self::EXTERNAL_REFUND_ID);

        return is_scalar($externalRefundId) ? (string)$externalRefundId : '';
    }

    public function setExternalRefundId(string $externalRefundId): void
    {
        $this->setData(self::EXTERNAL_REFUND_ID, $externalRefundId);
    }

    public function getStatus(): string
    {
        $status = $this->getData(self::STATUS);

        return is_scalar($status) ? (string)$status : '';
    }

    public function setStatus(string $status): void
    {
        $this->setData(self::STATUS, $status);
    }

    public function getDescription(): string
    {
        $description = $this->getData(self::DESCRIPTION);

        return is_scalar($description) ? (string)$description : '';
    }

    public function setDescription(string $description): void
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    public function getRefundAmount(): float
    {
        $refundAmount = $this->getData(self::REFUND_AMOUNT);

        return is_scalar($refundAmount) ? (float)$refundAmount : 0.00;
    }

    public function setRefundAmount(float $refundAmount): void
    {
        $this->setData(self::REFUND_AMOUNT, $refundAmount);
    }
}
