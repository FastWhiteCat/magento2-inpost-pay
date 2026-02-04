<?php

declare(strict_types=1);

namespace InPost\InPostPay\Enum;

/**
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
 */
enum InPostRefundStatus: string
{
    case PENDING = 'PENDING';
    case SUCCESS = 'SUCCESS';
    case FAILED = 'FAILED';
}
