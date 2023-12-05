<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Dto\Order;

use InPost\InPostPay\Model\Dto\Order\AddressDetailsFactory;

class DeliveryAddress
{
    public const NAME = 'name';
    public const COUNTRY_CODE = 'country_code';
    public const ADDRESS = 'address';
    public const CITY = 'city';
    public const POSTAL_CODE = 'postal_code';
    public const ADDRESS_DETAILS = 'address_details';

    private ?string $name;
    private ?string $countryCode;
    private ?string $address;
    private ?string $city;
    private ?string $postalCode;
    private ?AddressDetails $addressDetails;

    public function __construct(
        private readonly AddressDetailsFactory $addressDetailsFactory
    ) {
    }

    public function getName(): string
    {
        return (string)$this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCountryCode(): string
    {
        return (string)$this->countryCode;
    }

    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    public function getAddress(): string
    {
        return (string)$this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getPostalCode(): string
    {
        return (string)$this->postalCode;
    }

    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getAddressDetails(): AddressDetails
    {
        if ($this->addressDetails === null) {
            $this->addressDetails = $this->addressDetailsFactory->create();
        }

        return $this->addressDetails;
    }

    public function setAddressDetails(AddressDetails $addressDetails): void
    {
        $this->addressDetails = $addressDetails;
    }
}
