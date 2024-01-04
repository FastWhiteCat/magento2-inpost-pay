<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class OrderNotCreateException extends InPostPayException
{
    protected int $httpCode = 409;
    protected string $errorCode = 'ORDER_NOT_CREATE';
    protected string $errorMsg = 'Order not create.';

}
