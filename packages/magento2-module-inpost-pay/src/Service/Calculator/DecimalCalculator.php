<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Calculator;

class DecimalCalculator
{
    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
    public static function add(float|int $num, float|int $num2): float
    {
        return (float)bcadd(
            (string)$num,
            (string)$num2,
            4
        );
    }

    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
    public static function sub(float|int $num, float|int $num2): float
    {
        return (float)bcsub(
            (string)$num,
            (string)$num2,
            4
        );
    }

    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
    public static function mul(float|int $num, float|int $num2): float
    {
        return (float)bcmul(
            (string)$num,
            (string)$num2,
            4
        );
    }

    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
    public static function div(float|int $num, float|int $num2): float
    {
        return (float)bcdiv(
            (string)$num,
            (string)$num2,
            4
        );
    }

    // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
    public static function round(float|int $num, int $precision = 2): float
    {
        return round($num, $precision);
    }
}
