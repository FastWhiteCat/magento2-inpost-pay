<?php

declare(strict_types=1);

namespace InPost\InPostPay\Enum;

/**
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
 */
enum InPostDeliveryOption: string
{
    public const PWW_VALUE = 'Weekend Delivery';
    public const COD_VALUE = 'Cash on Delivery';
    public const CODPWW_VALUE = 'Cash on Delivery - Weekend';

    case PWW = 'Weekend Delivery';
    case COD = 'Cash on Delivery';
    case CODPWW = 'Cash on Delivery - Weekend';
}
