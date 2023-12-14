<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Phrase;

class InPostPayAuthorizationException extends Exception
{
    public const HTTP_UNAUTHORIZED = 401;
    private const AUTH_ERROR_MSG = 'Given user is not authorized to access the resource.';

    public function __construct(Phrase $phrase = null, $code = 0)
    {
        if ($phrase === null) {
            $msg = self::AUTH_ERROR_MSG;
            $phrase = new Phrase($msg);
        }
        parent::__construct(
            $phrase,
            $code,
            self::HTTP_UNAUTHORIZED
        );
    }
}
