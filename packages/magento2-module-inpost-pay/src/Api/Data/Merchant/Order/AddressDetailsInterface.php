<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Order;

interface AddressDetailsInterface
{
    public const STREET = 'street';
    public const BUILDING = 'building';
    public const FLAT = 'flat';

    /**
     * @return string
     */
    public function getStreet(): string;

    /**
     * @param string $street
     * @return void
     */
    public function setStreet(string $street): void;

    /**
     * @return string
     */
    public function getBuilding(): string;

    /**
     * @param string $building
     * @return void
     */
    public function setBuilding(string $building): void;

    /**
     * @return string
     */
    public function getFlat(): string;

    /**
     * @param string $flat
     * @return void
     */
    public function setFlat(string $flat): void;
}
