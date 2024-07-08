<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket\Product;

interface DeliveryProductInterface
{
    public const DELIVERY_TYPE = 'delivery_type';
    public const IF_DELIVERY_AVAILABLE = 'if_delivery_available';

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
     * @return bool
     */
    public function isIfDeliveryAvailable(): bool;

    /**
     * @param bool $ifDeliveryAvailable
     * @return void
     */
    public function setIfDeliveryAvailable(bool $ifDeliveryAvailable): void;
}
