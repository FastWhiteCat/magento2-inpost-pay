<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Dto\Order;

class AddressDetails
{
    public const STREET = 'street';
    public const BUILDING = 'building';
    public const FLAT = 'flat';

    private ?string $street;
    private ?string $building;
    private ?string $flat;

    public function getStreet(): string
    {
        return (string)$this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getBuilding(): string
    {
        return (string)$this->building;
    }

    public function setBuilding(string $building): void
    {
        $this->building = $building;
    }

    public function getFlat(): string
    {
        return (string)$this->flat;
    }

    public function setFlat(string $flat): void
    {
        $this->flat = $flat;
    }
}
