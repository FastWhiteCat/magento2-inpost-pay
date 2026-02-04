<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

class OnlyInAppException extends InPostPayException
{
    public const ERROR_CODE = 'ONLY_IN_APP';

    protected int $httpCode = 400;
    protected string $errorCode = self::ERROR_CODE;
    protected string $errorMsg = 'This action can only be performed in the InPost Pay Mobile App.';
}
