<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\Summary\NoticeInterface;

interface SummaryInterface
{
    public const BASKET_BASE_PRICE = 'basket_base_price';
    public const BASKET_FINAL_PRICE = 'basket_final_price';
    public const BASKET_PROMO_PRICE = 'basket_promo_price';
    public const CURRENCY = 'currency';
    public const BASKET_ADDITIONAL_INFORMATION = 'basket_additional_information';
    public const BASKET_EXPIRATION_DATE = 'basket_expiration_date';
    public const PAYMENT_TYPE = 'payment_type';
    public const BASKET_NOTICE = 'basket_notice';
    public const FREE_BASKET = 'free_basket';

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
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Summary\NoticeInterface|null
     */
    public function getBasketNotice(): ?NoticeInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Summary\NoticeInterface|null $basketNotice
     * @return void
     */
    public function setBasketNotice(?NoticeInterface $basketNotice): void;

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

    /**
     * @return string|null
     */
    public function getBasketExpirationDate(): ?string;

    /**
     * @param string|null $basketExpirationDate
     * @return void
     */
    public function setBasketExpirationDate(?string $basketExpirationDate): void;

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getFreeBasket(): bool;

    /**
     * @param bool $freeBasket
     * @return void
     */
    public function setFreeBasket(bool $freeBasket): void;
}
