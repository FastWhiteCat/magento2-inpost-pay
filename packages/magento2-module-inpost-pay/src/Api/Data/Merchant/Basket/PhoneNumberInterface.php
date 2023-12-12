<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket;

interface PhoneNumberInterface
{
    public const COUNTRY_PREFIX = 'country_prefix';
    public const PHONE = 'phone';

    /**
     * @return string
     */
    public function getCountryPrefix(): string;

    /**
     * @param string $countryPrefix
     * @return void
     */
    public function setCountryPrefix(string $countryPrefix): void;

    /**
     * @return string
     */
    public function getPhone(): string;

    /**
     * @param string $phone
     * @return void
     */
    public function setPhone(string $phone): void;
}
