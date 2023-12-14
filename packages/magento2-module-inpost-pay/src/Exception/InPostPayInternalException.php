<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Phrase;

class InPostPayInternalException extends Exception
{
    public const HTTP_INTERNAL_ERROR = 500;
    private const INTERNAL_ERROR_MSG = 'Something went wrong. Please try again later.';

    public function __construct(Phrase $phrase = null, $code = 0)
    {
        if ($phrase === null) {
            $msg = self::INTERNAL_ERROR_MSG;
            $phrase = new Phrase($msg);
        }
        parent::__construct(
            $phrase,
            $code,
            self::HTTP_INTERNAL_ERROR
        );
    }
}
