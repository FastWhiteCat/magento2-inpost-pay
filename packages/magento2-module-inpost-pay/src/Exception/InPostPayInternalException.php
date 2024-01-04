<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class InPostPayInternalException extends InPostPayException
{
    protected int $httpCode = 500;
    protected string $errorCode = 'INTERNAL_SERVER_ERROR';
    protected string $errorMsg = 'Something went wrong. Please try again later.';
}
