<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Provider;

interface PolishRegionProviderInterface
{
    public const POLAND_COUNTRY_CODE = 'PL';

    /**
     * @param string $postcode
     * @return string
     */
    public function getRegionNameByPostcode(string $postcode): string;

    /**
     * @param string $regionName
     * @return int
     */
    public function getRegionIdByName(string $regionName): int;
}
