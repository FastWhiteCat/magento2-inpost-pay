<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class OrderNotUpdateException extends InPostPayException
{
    public const ERROR_CODE = 'ORDER_NOT_UPDATE';

    protected int $httpCode = 409;
    protected string $errorCode = self::ERROR_CODE;
    protected string $errorMsg = 'Order not update.';
}
