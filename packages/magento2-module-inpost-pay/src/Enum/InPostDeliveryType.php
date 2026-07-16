<?php

declare(strict_types=1);

namespace InPost\InPostPay\Enum;

/**
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
 */
enum InPostDeliveryType: string
{
    public const APM_VALUE = 'APM';
    public const COURIER_VALUE = 'COURIER';

    case APM = 'APM';
    case COURIER = 'COURIER';
    case DIGITAL = 'DIGITAL';
}
