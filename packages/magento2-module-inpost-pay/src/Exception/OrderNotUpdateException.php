<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Phrase;

class OrderNotUpdateException extends Exception
{
    public const HTTP_CONFLICT = 409;
    private const ORDER_NOT_UPDATE_ERROR_MSG = 'Order not update.';

    public function __construct(
        Phrase $phrase = null,
        $code = 0
    ) {
        if ($phrase === null) {
            $msg = self::ORDER_NOT_UPDATE_ERROR_MSG;
            $phrase = new Phrase($msg);
        }

        parent::__construct(
            $phrase,
            $code,
            self::HTTP_CONFLICT
        );
    }
}
