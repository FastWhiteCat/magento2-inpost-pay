<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket;

interface SummaryInterface
{
    public const BASKET_BASE_PRICE = 'basket_base_price';
    public const BASKET_FINAL_PRICE = 'basket_final_price';
    public const BASKET_PROMO_PRICE = 'basket_promo_price';
    public const CURRENCY = 'currency';
    public const BASKET_ADDITIONAL_INFORMATION = 'basket_additional_information';
    public const PAYMENT_TYPE = 'payment_type';

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getBasketBasePrice(): PriceInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $basketBasePrice
     * @return void
     */
    public function setBasketBasePrice(PriceInterface $basketBasePrice): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getBasketFinalPrice(): PriceInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $basketFinalPrice
     * @return void
     */
    public function setBasketFinalPrice(PriceInterface $basketFinalPrice): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getBasketPromoPrice(): PriceInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $basketPromoPrice
     * @return void
     */
    public function setBasketPromoPrice(PriceInterface $basketPromoPrice): void;

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
    public function getBasketAdditionalInformation(): string;

    /**
     * @param string $basketAdditionalInformation
     * @return void
     */
    public function setBasketAdditionalInformation(string $basketAdditionalInformation): void;

    /**
     * @return string[]
     */
    public function getPaymentType(): array;

    /**
     * @param string[] $paymentType
     * @return void
     */
    public function setPaymentType(array $paymentType): void;
}
