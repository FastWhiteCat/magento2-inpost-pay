<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Refund;

use InPost\InPostPay\Api\Data\Merchant\Refund\EventData\AmountInterface;
use InPost\InPostPay\Api\Data\Merchant\Refund\EventData\PaymentInterface;

interface EventDataInterface
{
    public const OPERATION_ID = 'operationId';
    public const STATUS = 'status';
    public const AMOUNT = 'amount';
    public const PAYMENT = 'payment';
    public const MERCHANT_ID = 'merchantId';
    public const EVENT_DATE_TIME = 'eventDateTime';
    public const CREATED_DATE = 'createdDate';
    public const REFUND_REFERENCE = 'refundReference';

    /**
     * @return string|null
     */
    public function getOperationId(): ?string;

    /**
     * @param string|null $operationId
     * @return void
     */
    public function setOperationId(?string $operationId): void;

    /**
     * @return string|null
     */
    public function getStatus(): ?string;

    /**
     * @param string|null $status
     * @return void
     */
    public function setStatus(?string $status): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Refund\EventData\AmountInterface
     */
    public function getAmount(): AmountInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Refund\EventData\AmountInterface $amount
     * @return void
     */
    public function setAmount(AmountInterface $amount): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Refund\EventData\PaymentInterface
     */
    public function getPayment(): PaymentInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Refund\EventData\PaymentInterface $payment
     * @return void
     */
    public function setPayment(PaymentInterface $payment): void;

    /**
     * @return string
     */
    public function getMerchantId(): string;

    /**
     * @param string $merchantId
     * @return void
     */
    public function setMerchantId(string $merchantId): void;

    /**
     * @return string
     */
    public function getEventDateTime(): string;

    /**
     * @param string $eventDateTime
     * @return void
     */
    public function setEventDateTime(string $eventDateTime): void;

    /**
     * @return string
     */
    public function getCreatedDate(): string;

    /**
     * @param string $createdDate
     * @return void
     */
    public function setCreatedDate(string $createdDate): void;

    /**
     * @return string|null
     */
    public function getRefundReference(): ?string;

    /**
     * @param string|null $refundReference
     * @return void
     */
    public function setRefundReference(?string $refundReference): void;
}
