<?php

declare(strict_types=1);

namespace InPost\InPostPay\Enum;

/**
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
 */
enum InPostConsentRequirementType: string
{
    case OPTIONAL = 'OPTIONAL';
    case REQUIRED_ONCE = 'REQUIRED_ONCE';
    case REQUIRED_ALWAYS  = 'REQUIRED_ALWAYS';
}
