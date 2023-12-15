<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Order;

interface EventDataInterface
{
    public const PAYMENT_STATUS = 'payment_status';
    public const PAYMENT_ID = 'payment_id';
    public const PAYMENT_REFERENCE = 'payment_reference';
    public const PAYMENT_TYPE = 'payment_type';
    public const ORDER_STATUS = 'order_status';

    /**
     * @return string
     */
    public function getPaymentStatus(): string;

    /**
     * @param string $paymentStatus
     * @return void
     */
    public function setPaymentStatus(string $paymentStatus): void;

    /**
     * @return string
     */
    public function getPaymentId(): string;

    /**
     * @param string $paymentId
     * @return void
     */
    public function setPaymentId(string $paymentId): void;

    /**
     * @return string
     */
    public function getPaymentReference(): string;

    /**
     * @param string $paymentReference
     * @return void
     */
    public function setPaymentReference(string $paymentReference): void;

    /**
     * @return string
     */
    public function getPaymentType(): string;

    /**
     * @param string $paymentType
     * @return void
     */
    public function setPaymentType(string $paymentType): void;

    /**
     * @return string
     */
    public function getOrderStatus(): string;

    /**
     * @param string $orderStatus
     * @return void
     */
    public function setOrderStatus(string $orderStatus): void;
}
