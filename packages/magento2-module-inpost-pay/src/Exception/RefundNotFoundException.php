<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class RefundNotFoundException extends InPostPayException
{
    public const ERROR_CODE = 'REFUND_NOT_FOUND';

    protected int $httpCode = 404;
    protected string $errorCode = self::ERROR_CODE;
    protected string $errorMsg = 'Refund not found.';
}
