<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Phrase;

class InPostPayBadRequestException extends Exception
{
    public const HTTP_BAD_REQUEST = 400;
    private const BAD_REQUEST_ERROR_MSG = 'Invalid request.';

    public function __construct(Phrase $phrase = null, $code = 0)
    {
        if ($phrase === null) {
            $msg = self::BAD_REQUEST_ERROR_MSG;
            $phrase = new Phrase($msg);
        }
        parent::__construct(
            $phrase,
            $code,
            self::HTTP_BAD_REQUEST
        );
    }
}
