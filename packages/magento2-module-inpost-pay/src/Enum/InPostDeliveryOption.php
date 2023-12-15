<?php

declare(strict_types=1);

namespace InPost\InPostPay\Enum;

/**
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
 */
enum InPostDeliveryOption: string
{
    case PWW = 'Weekend Delivery';
    case COD = 'Cash on Delivery';
    case CODPWW = 'Cash on Delivery - Weekend';
}
