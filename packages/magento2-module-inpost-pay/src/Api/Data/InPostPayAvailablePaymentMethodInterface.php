<?php
declare(strict_types=1);

namespace InPost\InPostPay\Api\Data;

interface InPostPayAvailablePaymentMethodInterface
{
    public const TABLE_NAME = 'inpost_pay_available_payment_method';
    public const ENTITY_NAME = 'inpost_pay_available_payment_method';
    public const PAYMENT_METHOD_ID = 'payment_method_id';
    public const PAYMENT_CODE = 'payment_code';

    public function getPaymentMethodId(): ?int;
    public function setPaymentMethodId(int $paymentMethodId): InPostPayAvailablePaymentMethodInterface;

    public function getPaymentCode(): string;
    public function setPaymentCode(string $paymentCode): InPostPayAvailablePaymentMethodInterface;
}
