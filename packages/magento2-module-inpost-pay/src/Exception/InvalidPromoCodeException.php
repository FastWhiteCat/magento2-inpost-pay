<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class InvalidPromoCodeException extends InPostPayException
{
    public const ERROR_CODE = 'BASKET_NOT_UPDATE';

    protected int $httpCode = 409;
    protected string $errorCode = self::ERROR_CODE;
    protected string $errorMsg = 'Invalid promo code.';
}
