<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Order;

use InPost\InPostPay\Api\Data\Merchant\Order\EventDataInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class EventData extends DataObject implements EventDataInterface, ExtensibleDataInterface
{
    public function getPaymentStatus(): string
    {
        $paymentStatus = $this->getData(self::PAYMENT_STATUS);

        return (is_scalar($paymentStatus)) ? (string)$paymentStatus : '';
    }

    public function setPaymentStatus(string $paymentStatus): void
    {
        $this->setData(self::PAYMENT_STATUS, $paymentStatus);
    }

    public function getPaymentId(): string
    {
        $paymentId = $this->getData(self::PAYMENT_ID);

        return (is_scalar($paymentId)) ? (string)$paymentId : '';
    }

    public function setPaymentId(string $paymentId): void
    {
        $this->setData(self::PAYMENT_ID, $paymentId);
    }

    public function getPaymentReference(): string
    {
        $paymentReference = $this->getData(self::PAYMENT_REFERENCE);

        return (is_scalar($paymentReference)) ? (string)$paymentReference : '';
    }

    public function setPaymentReference(string $paymentReference): void
    {
        $this->setData(self::PAYMENT_REFERENCE, $paymentReference);
    }

    public function getPaymentType(): string
    {
        $paymentType = $this->getData(self::PAYMENT_TYPE);

        return (is_scalar($paymentType)) ? (string)$paymentType : '';
    }

    public function setPaymentType(string $paymentType): void
    {
        $this->setData(self::PAYMENT_TYPE, $paymentType);
    }

    public function getOrderStatus(): string
    {
        $orderStatus = $this->getData(self::ORDER_STATUS);

        return (is_scalar($orderStatus)) ? (string)$orderStatus : '';
    }

    public function setOrderStatus(string $orderStatus): void
    {
        $this->setData(self::ORDER_STATUS, $orderStatus);
    }
}
