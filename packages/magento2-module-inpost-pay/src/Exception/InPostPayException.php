<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Phrase;

class InPostPayException extends Exception
{
    public const INPOST_ERROR_CODE = 'inpost_error_code';
    public const INPOST_ERROR_MESSAGE = 'inpost_error_message';

    protected int $httpCode = 500;
    protected string $errorCode = '';
    protected string $errorMsg = '';

    public function __construct(Phrase $phrase = null)
    {
        if ($phrase === null) {
            $phrase = new Phrase($this->errorMsg);
        }
        parent::__construct(
            $phrase,
            0,
            $this->httpCode,
            [self::INPOST_ERROR_CODE => $this->errorCode]
        );
    }
}
