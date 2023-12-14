<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data;

use Magento\Framework\Exception\LocalizedException;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;;

interface InPostPayOrderInterface
{
    public const TABLE_NAME = 'inpost_pay_order';
    public const ENTITY_NAME = 'inpost_pay_order';
    public const INPOST_PAY_ORDER_ID = 'inpost_pay_order_id';
    public const ORDER_ID = 'order_id';
    public const LOCKER_ID = 'locker_id';
    public const ORDER_STATUS = 'order_status';
    public const COUNTRY_PREFIX = 'country_prefix';
    public const PHONE = 'phone';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    public function getInPostPayOrderId(): ?int;
    public function setInPostPayOrderId(int $inPostPayOrderId): InPostPayOrderInterface;

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getOrderId(): int;
    public function setOrderId(int $orderId): InPostPayOrderInterface;
    public function getLockerId(): ?string;
    public function setLockerId(string $lockerId): InPostPayOrderInterface;
    public function getOrderStatus(): ?string;
    public function setOrderStatus(string $orderStatus): InPostPayOrderInterface;
    public function getPhone(): ?string;
    public function setPhone(string $phone): InPostPayOrderInterface;
    public function getCountryPrefix(): ?string;
    public function setCountryPrefix(string $countryPrefix): InPostPayOrderInterface;
    public function getPhoneNumber(): PhoneNumberInterface;

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getCreatedAt(): string;

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getUpdatedAt(): string;
}
