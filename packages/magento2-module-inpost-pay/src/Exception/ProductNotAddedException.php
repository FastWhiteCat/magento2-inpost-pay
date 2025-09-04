<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class ProductNotAddedException extends InPostPayException
{
    public const ERROR_CODE = 'PRODUCT_NOT_ADDED';

    protected int $httpCode = 409;
    protected string $errorCode = self::ERROR_CODE;
    protected string $errorMsg = 'Could not add product to cart.';
}
