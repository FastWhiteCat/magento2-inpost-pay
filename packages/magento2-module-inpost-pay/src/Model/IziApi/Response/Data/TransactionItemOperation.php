<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Response\Data;

use Magento\Framework\DataObject;

class TransactionItemOperation extends DataObject
{
    public const EXTERNAL_OPERATION_ID = 'external_operation_id';
    public const TYPE = 'type';
    public const STATUS = 'status';
    public const AMOUNT = 'amount';
    public const CURRENCY = 'currency';
    public const OPERATION_DATE = 'operation_date';

    public function getExternalOperationId(): string
    {
        $externalOperationId = $this->getData(self::EXTERNAL_OPERATION_ID);

        return is_scalar($externalOperationId) ? (string)$externalOperationId : '';
    }
    public function setExternalOperationId(string $externalOperationId): void
    {
        $this->setData(self::EXTERNAL_OPERATION_ID, $externalOperationId);
    }

    public function getType(): string
    {
        $type = $this->getData(self::TYPE);

        return is_scalar($type) ? (string)$type : '';
    }

    public function setType(string $type): void
    {
        $this->setData(self::TYPE, $type);
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

    public function getAmount(): float
    {
        $amount = $this->getData(self::AMOUNT);

        return is_scalar($amount) ? (float)$amount : 0.00;
    }

    public function setAmount(float $amount): void
    {
        $this->setData(self::AMOUNT, $amount);
    }

    public function getCurrency(): string
    {
        $currency = $this->getData(self::CURRENCY);

        return is_scalar($currency) ? (string)$currency : '';
    }

    public function setCurrency(string $currency): void
    {
        $this->setData(self::CURRENCY, $currency);
    }

    public function getOperationDate(): string
    {
        $operationDate = $this->getData(self::OPERATION_DATE);

        return is_scalar($operationDate) ? (string)$operationDate : '';
    }

    public function setOperationDate(string $operationDate): void
    {
        $this->setData(self::OPERATION_DATE, $operationDate);
    }
}
