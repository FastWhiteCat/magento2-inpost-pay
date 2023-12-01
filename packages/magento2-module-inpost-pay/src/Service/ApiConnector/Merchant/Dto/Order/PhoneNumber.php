<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order;

class PhoneNumber
{
    public const PHONE = 'phone';
    public const COUNTRY_PREFIX = 'country_prefix';

    private ?string $phone;
    private ?string $countryPrefix;

    public function getPhone(): string
    {
        return (string)$this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getCountryPrefix(): string
    {
        return (string)$this->countryPrefix;
    }

    public function setCountryPrefix(string $countryPrefix): void
    {
        $this->countryPrefix = $countryPrefix;
    }
}
