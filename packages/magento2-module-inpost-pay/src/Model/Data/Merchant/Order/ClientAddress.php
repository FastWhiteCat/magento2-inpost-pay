<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Order;

use InPost\InPostPay\Api\Data\Merchant\Order\AddressDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AddressDetailsInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Order\ClientAddressInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class ClientAddress extends DataObject implements ClientAddressInterface, ExtensibleDataInterface
{
    private const DEFAULT_COUNTRY_CODE = 'PL';

    /**
     * @param AddressDetailsInterfaceFactory $addressDetailsFactory
     * @param array $data
     */
    public function __construct(
        private readonly AddressDetailsInterfaceFactory $addressDetailsFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        $countryCode = $this->getData(self::COUNTRY_CODE);

        return (is_scalar($countryCode)) ? (string)$countryCode : self::DEFAULT_COUNTRY_CODE;
    }

    /**
     * @param string $countryCode
     * @return void
     */
    public function setCountryCode(string $countryCode): void
    {
        $this->setData(self::COUNTRY_CODE, $countryCode);
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        $address = $this->getData(self::ADDRESS);

        return (is_scalar($address)) ? (string)$address : '';
    }

    /**
     * @param string $address
     * @return void
     */
    public function setAddress(string $address): void
    {
        $this->setData(self::ADDRESS, $address);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Order\AddressDetailsInterface
     */
    public function getAddressDetails(): AddressDetailsInterface
    {
        $addressDetails = $this->getData(self::ADDRESS_DETAILS);

        if ($addressDetails instanceof AddressDetailsInterface) {
            return $addressDetails;
        }

        return $this->addressDetailsFactory->create();
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\AddressDetailsInterface $addressDetails
     * @return void
     */
    public function setAddressDetails(AddressDetailsInterface $addressDetails): void
    {
        $this->setData(self::ADDRESS_DETAILS, $addressDetails);
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        $city = $this->getData(self::CITY);

        return (is_scalar($city)) ? (string)$city : '';
    }

    /**
     * @param string $city
     * @return void
     */
    public function setCity(string $city): void
    {
        $this->setData(self::CITY, $city);
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        $postalCode = $this->getData(self::POSTAL_CODE);

        return (is_scalar($postalCode)) ? (string)$postalCode : '';
    }

    /**
     * @param string $postalCode
     * @return void
     */
    public function setPostalCode(string $postalCode): void
    {
        $this->setData(self::POSTAL_CODE, $postalCode);
    }
}
