<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket\Product;

interface QuantityInterface
{
    public const QUANTITY = 'quantity';
    public const QUANTITY_TYPE = 'quantity_type';
    public const QUANTITY_UNIT = 'quantity_unit';
    public const AVAILABLE_QUANTITY = 'available_quantity';
    public const MAX_QUANTITY = 'max_quantity';

    /**
     * @return float|int
     */
    public function getQuantity(): float|int;

    /**
     * @param float|int $quantity
     * @return void
     */
    public function setQuantity(float|int $quantity): void;

    /**
     * @return string
     */
    public function getQuantityType(): string;

    /**
     * @param string $quantityType
     * @return void
     */
    public function setQuantityType(string $quantityType): void;

    /**
     * @return string
     */
    public function getQuantityUnit(): string;

    /**
     * @param string $quantityUnit
     * @return void
     */
    public function setQuantityUnit(string $quantityUnit): void;

    /**
     * @return float
     */
    public function getAvailableQuantity(): float;

    /**
     * @param float $availableQuantity
     * @return void
     */
    public function setAvailableQuantity(float $availableQuantity): void;

    /**
     * @return float
     */
    public function getMaxQuantity(): float;

    /**
     * @param float $maxQuantity
     * @return void
     */
    public function setMaxQuantity(float $maxQuantity): void;
}
