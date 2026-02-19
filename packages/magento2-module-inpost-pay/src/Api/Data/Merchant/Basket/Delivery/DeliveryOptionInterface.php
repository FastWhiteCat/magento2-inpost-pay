<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket\Delivery;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;

interface DeliveryOptionInterface
{
    public const DELIVERY_NAME = 'delivery_name';
    public const DELIVERY_CODE_VALUE = 'delivery_code_value';
    public const DELIVERY_OPTION_PRICE = 'delivery_option_price';

    /**
     * @return string
     */
    public function getDeliveryName(): string;

    /**
     * @param string $deliveryName
     * @return void
     */
    public function setDeliveryName(string $deliveryName): void;

    /**
     * @return string
     */
    public function getDeliveryCodeValue(): string;

    /**
     * @param string $deliveryCodeValue
     * @return void
     */
    public function setDeliveryCodeValue(string $deliveryCodeValue): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getDeliveryOptionPrice(): PriceInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $deliveryOptionPrice
     * @return void
     */
    public function setDeliveryOptionPrice(PriceInterface $deliveryOptionPrice): void;
}
