<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class InPostPayAuthorizationException extends InPostPayException
{
    public const ERROR_CODE = 'UNAUTHORIZED';

    protected int $httpCode = 401;
    protected string $errorCode = self::ERROR_CODE;
    protected string $errorMsg = 'Given user is not authorized to access the resource.';
}
