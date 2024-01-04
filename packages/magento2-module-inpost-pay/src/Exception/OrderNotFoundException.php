<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class OrderNotFoundException extends InPostPayException
{
    protected int $httpCode = 404;
    protected string $errorCode = 'ORDER_NOT_FOUND';
    protected string $errorMsg = 'Order not found.';
}
