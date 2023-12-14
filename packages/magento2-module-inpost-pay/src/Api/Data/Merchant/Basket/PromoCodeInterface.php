<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket;

interface PromoCodeInterface
{
    public const NAME = 'name';
    public const PROMO_CODE_VALUE = 'promo_code_value';

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void;

    /**
     * @return string
     */
    public function getPromoCodeValue(): string;

    /**
     * @param string $promoCodeValue
     * @return void
     */
    public function setPromoCodeValue(string $promoCodeValue): void;
}
