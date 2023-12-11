<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

class InPostPayOrder extends AbstractModel implements InPostPayOrderInterface
{
    protected $_eventPrefix = InPostPayOrderInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayOrderInterface::ENTITY_NAME;

    public function _construct(): void
    {
        $this->_init(ResourceModel\InPostPayOrder::class);
    }

    public function getInPostPayOrderId(): ?int
    {
        $id = ($this->hasData(self::INPOST_PAY_ORDER_ID)) ? $this->getData(self::INPOST_PAY_ORDER_ID) : null;

        return ($id && is_scalar($id)) ? (int)$id : null;
    }

    public function setInPostPayOrderId(int $inPostPayOrderId): InPostPayOrderInterface
    {
        return $this->setData(self::INPOST_PAY_ORDER_ID, $inPostPayOrderId);
    }

    public function getOrderId(): int
    {
        $orderId = $this->getData(self::ORDER_ID);

        if ($orderId && is_scalar($orderId)) {
            return (int)$orderId;
        }

        throw new LocalizedException(__('Invalid InPost Pay Order ID value.'));
    }

    public function setOrderId(int $orderId): InPostPayOrderInterface
    {
        return $this->setData(self::INPOST_PAY_ORDER_ID, $orderId);
    }

    public function getLockerId(): ?string
    {
        $lockerId = ($this->hasData(self::LOCKER_ID)) ? $this->getData(self::LOCKER_ID) : null;

        return ($lockerId && is_scalar($lockerId)) ? (string)$lockerId : null;
    }

    public function setLockerId(string $lockerId): InPostPayOrderInterface
    {
        return $this->setData(self::LOCKER_ID, $lockerId);
    }

    public function getOrderStatus(): ?string
    {
        $orderStatus = ($this->hasData(self::ORDER_STATUS)) ? $this->getData(self::ORDER_STATUS) : null;

        return ($orderStatus && is_scalar($orderStatus)) ? (string)$orderStatus : null;
    }

    public function setOrderStatus(string $orderStatus): InPostPayOrderInterface
    {
        return $this->setData(self::ORDER_STATUS, $orderStatus);
    }

    public function getPhoneNumber(): ?string
    {
        $phoneNumber = ($this->hasData(self::PHONE_NUMBER)) ? $this->getData(self::PHONE_NUMBER) : null;

        return ($phoneNumber && is_scalar($phoneNumber)) ? (string)$phoneNumber : null;
    }

    public function setPhoneNumber(string $phoneNumber): InPostPayOrderInterface
    {
        return $this->setData(self::PHONE_NUMBER, $phoneNumber);
    }

    public function getPrefix(): ?string
    {
        $prefix = ($this->hasData(self::PREFIX)) ? $this->getData(self::PREFIX) : null;

        return ($prefix && is_scalar($prefix)) ? (string)$prefix : null;
    }

    public function setPrefix(string $prefix): InPostPayOrderInterface
    {
        return $this->setData(self::PREFIX, $prefix);
    }

    public function getCreatedAt(): string
    {
        $createdAt = $this->getData(self::CREATED_AT);

        if ($createdAt && is_scalar($createdAt)) {
            return (string)$createdAt;
        }

        throw new LocalizedException(__('Invalid InPost Pay Order created at value.'));
    }

    public function getUpdatedAt(): string
    {
        $updatedAt = $this->getData(self::UPDATED_AT);

        if ($updatedAt && is_scalar($updatedAt)) {
            return (string)$updatedAt;
        }

        throw new LocalizedException(__('Invalid InPost Pay Order updated at value.'));
    }
}
