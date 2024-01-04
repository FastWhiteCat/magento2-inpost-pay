<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class BasketNotFoundException extends InPostPayException
{
    protected int $httpCode = 404;
    protected string $errorCode = 'BASKET_NOT_FOUND';
    protected string $errorMsg = 'Basket not found.';
}
