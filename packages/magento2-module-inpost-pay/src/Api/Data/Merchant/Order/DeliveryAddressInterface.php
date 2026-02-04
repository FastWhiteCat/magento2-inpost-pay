<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Order;

interface DeliveryAddressInterface
{
    public const NAME = 'name';
    public const COUNTRY_CODE = 'country_code';
    public const ADDRESS = 'address';
    public const CITY = 'city';
    public const POSTAL_CODE = 'postal_code';
    public const ADDRESS_DETAILS = 'address_details';

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
    public function getCountryCode(): string;

    /**
     * @param string $countryCode
     * @return void
     */
    public function setCountryCode(string $countryCode): void;

    /**
     * @return string
     */
    public function getAddress(): string;

    /**
     * @param string $address
     * @return void
     */
    public function setAddress(string $address): void;

    /**
     * @return string
     */
    public function getCity(): string;

    /**
     * @param string $city
     * @return void
     */
    public function setCity(string $city): void;

    /**
     * @return string
     */
    public function getPostalCode(): string;

    /**
     * @param string $postalCode
     * @return void
     */
    public function setPostalCode(string $postalCode): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Order\AddressDetailsInterface
     */
    public function getAddressDetails(): AddressDetailsInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\AddressDetailsInterface $addressDetails
     * @return void
     */
    public function setAddressDetails(AddressDetailsInterface $addressDetails): void;
}
