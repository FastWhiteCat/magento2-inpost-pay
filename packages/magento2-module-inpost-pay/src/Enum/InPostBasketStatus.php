<?php

declare(strict_types=1);

namespace InPost\InPostPay\Enum;

/**
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
 */
enum InPostBasketStatus: string
{
    case PENDING = 'PENDING';
    case SUCCESS = 'SUCCESS';
    case REJECT  = 'REJECT';
}
