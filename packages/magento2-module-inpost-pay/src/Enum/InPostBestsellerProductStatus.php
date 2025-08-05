<?php

declare(strict_types=1);

namespace InPost\InPostPay\Enum;

/**
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
 */
enum InPostBestsellerProductStatus: string
{
    public const ACTIVE_VALUE = 'ACTIVE';
    public const INACTIVE_VALUE = 'INACTIVE';
    public const UNKNOWN_VALUE = 'UNKNOWN';

    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
    case UNKNOWN = 'UNKNOWN';
}
