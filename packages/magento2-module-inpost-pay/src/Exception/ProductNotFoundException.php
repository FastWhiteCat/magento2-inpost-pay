<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class ProductNotFoundException extends InPostPayException
{
    public const ERROR_CODE = 'PRODUCT_NOT_FOUND';

    protected int $httpCode = 404;
    protected string $errorCode = self::ERROR_CODE;
    protected string $errorMsg = 'Product not found.';
}
