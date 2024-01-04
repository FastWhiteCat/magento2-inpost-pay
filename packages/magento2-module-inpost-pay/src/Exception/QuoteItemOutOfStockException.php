<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class QuoteItemOutOfStockException extends InPostPayException
{
    protected int $httpCode = 409;
    protected string $errorCode = 'ORDER_NOT_CREATE';
    protected string $errorMsg = 'Product is no longer available in requested quantity.';
}
