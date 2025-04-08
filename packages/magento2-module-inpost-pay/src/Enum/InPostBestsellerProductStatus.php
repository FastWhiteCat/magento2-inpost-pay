<?php

declare(strict_types=1);

namespace InPost\InPostPay\Enum;

/**
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
 */
enum InPostBestsellerProductStatus: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
    case UNKNOWN = 'UNKNOWN';
}
