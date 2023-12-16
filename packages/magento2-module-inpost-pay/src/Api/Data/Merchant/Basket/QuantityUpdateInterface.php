<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\Product\QuantityChangeInterface;

interface QuantityUpdateInterface
{
    public const PRODUCT_ID = 'product_id';
    public const EAN = 'ean';
    public const QUANTITY = 'quantity';

    /**
     * @return string
     */
    public function getProductId(): string;

    /**
     * @param string $productId
     * @return void
     */
    public function setProductId(string $productId): void;

    /**
     * @return string|null
     */
    public function getEan(): ?string;

    /**
     * @param string|null $ean
     * @return void
     */
    public function setEan(?string $ean): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Product\QuantityChangeInterface
     */
    public function getQuantity(): QuantityChangeInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Product\QuantityChangeInterface $quantity
     * @return void
     */
    public function setQuantity(QuantityChangeInterface $quantity): void;
}
