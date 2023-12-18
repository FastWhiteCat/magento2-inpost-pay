<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Phrase;

class BasketNotFoundException extends Exception
{
    public const HTTP_NOT_FOUND = 404;
    private const ORDER_NOT_FOUND_ERROR_MSG = 'Basket not found.';

    public function __construct(Phrase $phrase = null, $code = 0)
    {
        if ($phrase === null) {
            $msg = self::ORDER_NOT_FOUND_ERROR_MSG;
            $phrase = new Phrase($msg);
        }
        parent::__construct(
            $phrase,
            $code,
            self::HTTP_NOT_FOUND
        );
    }
}
