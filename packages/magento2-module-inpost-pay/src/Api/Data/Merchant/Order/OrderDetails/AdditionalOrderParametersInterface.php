<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Order\OrderDetails;

interface AdditionalOrderParametersInterface
{
    public const KEY = 'key';
    public const VALUE = 'value';

    /**
     * @return string
     */
    public function getKey(): string;

    /**
     * @param string $key
     * @return void
     */
    public function setKey(string $key): void;

    /**
     * @return string
     */
    public function getValue(): string;

    /**
     * @param string $value
     * @return void
     */
    public function setValue(string $value): void;
}
