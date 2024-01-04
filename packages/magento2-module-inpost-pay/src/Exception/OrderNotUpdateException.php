<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class OrderNotUpdateException extends InPostPayException
{
    protected int $httpCode = 409;
    protected string $errorCode = 'ORDER_NOT_UPDATE';
    protected string $errorMsg = 'Order not update.';
}
