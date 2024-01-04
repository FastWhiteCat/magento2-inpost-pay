<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class InPostPayBadRequestException extends InPostPayException
{
    protected int $httpCode = 400;
    protected string $errorCode = 'BAD_REQUEST';
    protected string $errorMsg = 'Invalid request.';
}
