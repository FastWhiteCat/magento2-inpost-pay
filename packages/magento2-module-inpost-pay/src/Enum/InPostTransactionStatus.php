<?php

declare(strict_types=1);

namespace InPost\InPostPay\Enum;

/**
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
 */
enum InPostTransactionStatus: string
{
    case PENDING = 'PENDING';
    case AUTHORIZED = 'AUTHORIZED';
    case CARD_VERIFIED = 'CARD_VERIFIED';
    case CAPTURED = 'CAPTURED';
    case REFUNDED = 'REFUNDED';
    case PARTIALLY_REFUNDED = 'PARTIALLY_REFUNDED';
    case CANCELLED = 'CANCELLED';
    case DECLINED = 'DECLINED';
}
