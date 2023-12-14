<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data;

use Magento\Framework\Exception\LocalizedException;

interface InPostPayOrderInterface
{
    public const TABLE_NAME = 'inpost_pay_order';
    public const ENTITY_NAME = 'inpost_pay_order';
    public const INPOST_PAY_ORDER_ID = 'inpost_pay_order_id';
    public const ORDER_ID = 'order_id';
    public const BASKET_ID = 'basket_id';
    public const PAYMENT_TYPE = 'payment_type';
    public const LOCKER_ID = 'locker_id';
    public const DELIVERY_OPTIONS = 'delivery_options';
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
    public function getBasketId(): ?string;
    public function setBasketId(?string $basketId): InPostPayOrderInterface;
    public function getPaymentType(): ?string;
    public function setPaymentType(?string $paymentType): InPostPayOrderInterface;
    public function getLockerId(): ?string;
    public function setLockerId(string $lockerId): InPostPayOrderInterface;
    public function getDeliveryOptions(): array;
    public function setDeliveryOptions(array $deliveryOptions): InPostPayOrderInterface;

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
