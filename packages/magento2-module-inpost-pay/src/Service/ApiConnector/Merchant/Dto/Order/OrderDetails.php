<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order;

use InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order\BasketPriceFactory;

class OrderDetails
{
    public const BASKET_ID = 'basket_id';
    public const CURRENCY = 'currency';
    public const BASKET_PRICE = 'basket_price';
    public const PAYMENT_TYPE = 'payment_type';

    private ?string $basketId;
    private ?BasketPrice $basketPrice;
    private ?string $currency;
    private ?string $paymentType;

    public function __construct(
        private readonly BasketPriceFactory $basketPriceFactory
    ) {
    }

    public function getBasketId(): string
    {
        return (string)$this->basketId;
    }

    public function setBasketId(string $basketId): void
    {
        $this->basketId= $basketId;
    }

    public function getBasketPrice(): BasketPrice
    {
        if ($this->basketPrice === null) {
            $this->basketPrice = $this->basketPriceFactory->create();
        }

        return $this->basketPrice;
    }

    public function setBasketPrice(BasketPrice $basketPrice): void
    {
        $this->basketPrice = $basketPrice;
    }

    public function getCurrency(): string
    {
        return (string)$this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getPaymentType(): string
    {
        return (string)$this->paymentType;
    }

    public function setPaymentType(string $paymentType): void
    {
        $this->paymentType = $paymentType;
    }
}
