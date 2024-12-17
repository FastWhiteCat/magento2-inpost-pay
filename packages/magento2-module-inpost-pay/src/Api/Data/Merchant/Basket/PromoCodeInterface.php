<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket;

interface PromoCodeInterface
{
    public const NAME = 'name';
    public const PROMO_CODE_VALUE = 'promo_code_value';
    public const REGULATION_TYPE = 'regulation_type';
    public const REGULATION_TYPE_OMNIBUS = 'OMNIBUS';

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

    /**
     * @return string|null
     */
    public function getRegulationType(): ?string;

    /**
     * @param string|null $regulationType
     * @return void
     */
    public function setRegulationType(?string $regulationType): void;
}
