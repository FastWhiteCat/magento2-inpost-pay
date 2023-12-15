<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Order;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;

interface OrderDetailsInterface
{
    public const BASKET_ID = 'basket_id';
    public const CURRENCY = 'currency';
    public const BASKET_PRICE = 'basket_price';
    public const PAYMENT_TYPE = 'payment_type';
    public const ORDER_COMMENTS = 'order_comments';
    public const COMMENTS = 'comments';

    /**
     * @return string
     */
    public function getBasketId(): string;

    /**
     * @param string $basketId
     * @return void
     */
    public function setBasketId(string $basketId): void;

    /**
     * @return string
     */
    public function getOrderComments(): string;

    /**
     * @param string $orderComments
     * @return void
     */
    public function setOrderComments(string $orderComments): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getBasketPrice(): PriceInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $basketPrice
     * @return void
     */
    public function setBasketPrice(PriceInterface $basketPrice): void;

    /**
     * @return string
     */
    public function getCurrency(): string;

    /**
     * @param string $currency
     * @return void
     */
    public function setCurrency(string $currency): void;

    /**
     * @return string
     */
    public function getPaymentType(): string;

    /**
     * @param string $paymentType
     * @return void
     */
    public function setPaymentType(string $paymentType): void;
}
