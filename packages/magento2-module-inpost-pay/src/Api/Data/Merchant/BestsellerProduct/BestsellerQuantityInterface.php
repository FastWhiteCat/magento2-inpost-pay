<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\BestsellerProduct;

interface BestsellerQuantityInterface
{
    public const QUANTITY_TYPE = 'quantity_type';
    public const QUANTITY_UNIT = 'quantity_unit';
    public const AVAILABLE_QUANTITY = 'available_quantity';

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
     * @return string|null
     */
    public function getQuantityUnit(): ?string;

    /**
     * @param string|null $quantityUnit
     * @return void
     */
    public function setQuantityUnit(?string $quantityUnit): void;

    /**
     * @return float
     */
    public function getAvailableQuantity(): float;

    /**
     * @param float $availableQuantity
     * @return void
     */
    public function setAvailableQuantity(float $availableQuantity): void;
}
