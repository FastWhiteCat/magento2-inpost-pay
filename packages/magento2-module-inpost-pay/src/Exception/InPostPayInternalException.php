<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class InPostPayInternalException extends InPostPayException
{
    public const ERROR_CODE = 'INTERNAL_SERVER_ERROR';

    protected int $httpCode = 500;
    protected string $errorCode = self::ERROR_CODE;
    protected string $errorMsg = 'Something went wrong. Please try again later.';
}
