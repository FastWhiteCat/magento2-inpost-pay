<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;

interface DeliveryInterface
{
    public const DELIVERY_TYPE = 'delivery_type';
    public const DELIVERY_DATE = 'delivery_date';
    public const DELIVERY_OPTIONS = 'delivery_options';
    public const DELIVERY_PRICE = 'delivery_price';
    public const FREE_DELIVERY_MINIMUM_GROSS_PRICE = 'free_delivery_minimum_gross_price';

    /**
     * @return string
     */
    public function getDeliveryType(): string;

    /**
     * @param string $deliveryType
     * @return void
     */
    public function setDeliveryType(string $deliveryType): void;

    /**
     * @return string
     */
    public function getDeliveryDate(): string;

    /**
     * @param string $deliveryDate
     * @return void
     */
    public function setDeliveryDate(string $deliveryDate): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterface[]
     */
    public function getDeliveryOptions(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterface[] $deliveryOptions
     * @return void
     */
    public function setDeliveryOptions(array $deliveryOptions): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getDeliveryPrice(): PriceInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $deliveryPrice
     * @return void
     */
    public function setDeliveryPrice(PriceInterface $deliveryPrice): void;

    /**
     * @return float|null
     */
    public function getFreeDeliveryMinimumGrossPrice(): ?float;

    /**
     * @param float $freeDeliveryMinimumGrossPrice
     * @return void
     */
    public function setFreeDeliveryMinimumGrossPrice(float $freeDeliveryMinimumGrossPrice): void;
}
