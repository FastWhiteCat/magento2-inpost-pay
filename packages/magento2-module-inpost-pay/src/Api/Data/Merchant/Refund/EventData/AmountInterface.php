<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Refund\EventData;

interface AmountInterface
{
    public const VALUE_IN_CENTS = 'valueInCents';
    public const CURRENCY = 'currency';

    /**
     * @return int
     */
    public function getValueInCents(): int;

    /**
     * @param int $valueInCents
     * @return void
     */
    public function setValueInCents(int $valueInCents): void;

    /**
     * @return string
     */
    public function getCurrency(): string;

    /**
     * @param string $currency
     * @return void
     */
    public function setCurrency(string $currency): void;
}
