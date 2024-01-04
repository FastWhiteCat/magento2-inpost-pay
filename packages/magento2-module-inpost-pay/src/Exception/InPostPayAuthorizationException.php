<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class InPostPayAuthorizationException extends InPostPayException
{
    protected int $httpCode = 401;
    protected string $errorCode = 'UNAUTHORIZED';
    protected string $errorMsg = 'Given user is not authorized to access the resource.';
}
