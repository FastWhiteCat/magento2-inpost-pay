<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class QuoteItemOutOfStockException extends InPostPayException
{
    public const ERROR_CODE = 'ORDER_NOT_CREATE';

    protected int $httpCode = 409;
    protected string $errorCode = self::ERROR_CODE;
    protected string $errorMsg = 'Product is no longer available in requested quantity.';
}
