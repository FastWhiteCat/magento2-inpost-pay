<?php

declare(strict_types=1);

namespace InPost\InPostPay\Enum;

/**
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
 */
enum InPostDeliveryType: string
{
    case APM = 'APM';
    case COURIER = 'COURIER';
}
