<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Order;

interface AddressDetailsInterface
{
    public const STREET = 'street';
    public const BUILDING = 'building';
    public const FLAT = 'flat';

    /**
     * @return string|null
     */
    public function getStreet(): ?string;

    /**
     * @param string|null $street
     * @return void
     */
    public function setStreet(?string $street): void;

    /**
     * @return string|null
     */
    public function getBuilding(): ?string;

    /**
     * @param string|null $building
     * @return void
     */
    public function setBuilding(?string $building): void;

    /**
     * @return string|null
     */
    public function getFlat(): ?string;

    /**
     * @param string|null $flat
     * @return void
     */
    public function setFlat(?string $flat): void;
}
