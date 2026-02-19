<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class InPostPayBadRequestException extends InPostPayException
{
    public const ERROR_CODE = 'BAD_REQUEST';

    protected int $httpCode = 400;
    protected string $errorCode = self::ERROR_CODE;
    protected string $errorMsg = 'Invalid request.';
}
