<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Response\Data;

use Magento\Framework\DataObject;

class TransactionItem extends DataObject
{
    public const TRANSACTION_ID = 'transaction_id';
    public const MERCHANT_POS_ID = 'merchant_pos_id';
    public const EXTERNAL_TRANSACTION_ID = 'external_transaction_id';
    public const DESCRIPTION = 'description';
    public const STATUS = 'status';
    public const CREATED_DATE = 'created_date';
    public const AMOUNT = 'amount';
    public const CURRENCY = 'currency';
    public const PAYMENT_METHOD = 'payment_method';
    public const ORDER_ID = 'order_id';
    public const OPERATIONS = 'operations';

    public function getTransactionId(): string
    {
        $transactionId = $this->getData(self::TRANSACTION_ID);

        return is_scalar($transactionId) ? (string)$transactionId : '';
    }

    public function setTransactionId(string $transactionId): void
    {
        $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    public function getMerchantPosId(): string
    {
        $merchantPosId = $this->getData(self::MERCHANT_POS_ID);

        return is_scalar($merchantPosId) ? (string)$merchantPosId : '';
    }

    public function setMerchantPosId(string $merchantPosId): void
    {
        $this->setData(self::MERCHANT_POS_ID, $merchantPosId);
    }

    public function getExternalTransactionId(): string
    {
        $externalTransactionId = $this->getData(self::EXTERNAL_TRANSACTION_ID);

        return is_scalar($externalTransactionId) ? (string)$externalTransactionId : '';
    }

    public function setExternalTransactionId(string $externalTransactionId): void
    {
        $this->setData(self::EXTERNAL_TRANSACTION_ID, $externalTransactionId);
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

    public function getStatus(): string
    {
        $status = $this->getData(self::STATUS);

        return is_scalar($status) ? (string)$status : '';
    }

    public function setStatus(string $status): void
    {
        $this->setData(self::STATUS, $status);
    }

    public function getCreatedDate(): string
    {
        $createdDate = $this->getData(self::CREATED_DATE);

        return is_scalar($createdDate) ? (string)$createdDate : '';
    }

    public function setCreatedDate(string $createdDate): void
    {
        $this->setData(self::CREATED_DATE, $createdDate);
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

    public function getPaymentMethod(): string
    {
        $paymentMethod = $this->getData(self::PAYMENT_METHOD);

        return is_scalar($paymentMethod) ? (string)$paymentMethod : '';
    }

    public function setPaymentMethod(string $paymentMethod): void
    {
        $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }

    public function getOrderId(): string
    {
        $orderId = $this->getData(self::ORDER_ID);

        return is_scalar($orderId) ? (string)$orderId : '';
    }

    public function setOrderId(string $orderId): void
    {
        $this->setData(self::ORDER_ID, $orderId);
    }

    public function getOperations(): array
    {
        $operations = $this->getData(self::OPERATIONS);

        return is_array($operations) ? $operations : [];
    }

    public function setOperations(array $operations): void
    {
        $this->setData(self::OPERATIONS, $operations);
    }
}
