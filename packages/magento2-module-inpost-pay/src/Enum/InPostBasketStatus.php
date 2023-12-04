<?php

declare(strict_types=1);

namespace InPost\InPostPay\Enum;

enum InPostBasketStatus: string
{
    case PENDING = 'PENDING';
    case SUCCESS = 'SUCCESS';
    case REJECT  = 'REJECT';
}
