<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket\Product;

interface QuantityChangeInterface
{
    public const QUANTITY = 'quantity';

    /**
     * @return float|int
     */
    public function getQuantity(): float|int;

    /**
     * @param float|int $quantity
     * @return void
     */
    public function setQuantity(float|int $quantity): void;
}
