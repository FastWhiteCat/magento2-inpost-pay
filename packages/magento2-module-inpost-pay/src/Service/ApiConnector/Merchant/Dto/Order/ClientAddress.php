<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order;

use InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order\AddressDetailsFactory;

class ClientAddress
{
    public const COUNTRY_CODE = 'country_code';
    public const ADDRESS = 'address';
    public const ADDRESS_DETAILS = 'address_details';
    public const CITY = 'city';
    public const POSTAL_CODE = 'city';

    private ?string $countryCode;
    private ?string $address;
    private ?AddressDetails $addressDetails;
    private ?string $city;
    private ?string $postalCode;

    public function __construct(
        private readonly AddressDetailsFactory $addressDetailsFactory
    ) {
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

    public function getCity(): string
    {
        return (string)$this->city;
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
}
