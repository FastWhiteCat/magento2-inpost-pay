<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class OrderNotFoundException extends InPostPayException
{
    public const ERROR_CODE = 'ORDER_NOT_FOUND';

    protected int $httpCode = 404;
    protected string $errorCode = self::ERROR_CODE;
    protected string $errorMsg = 'Order not found.';
}
