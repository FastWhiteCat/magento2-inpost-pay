<?php
declare(strict_types=1);

namespace InPost\InPostPay\Model\Dto\Order;

use InPost\InPostPay\Model\Dto\Order\AccountInfoFactory;
use InPost\InPostPay\Model\Dto\Order\AddressDetailsFactory;
use InPost\InPostPay\Model\Dto\Order\ClientAddressFactory;
use InPost\InPostPay\Model\Dto\Order\PhoneNumberFactory;

class DtoAccountInfoFactory
{
    public function __construct(
        private readonly AccountInfoFactory $accountInfoFactory,
        private readonly PhoneNumberFactory $phoneNumberFactory,
        private readonly ClientAddressFactory $clientAddressFactory,
        private readonly AddressDetailsFactory $addressDetailsFactory
    ) {
    }

    public function create(array $data): AccountInfo
    {
        /** @var AccountInfo $accountInfo */
        $accountInfo = $this->accountInfoFactory->create();

        if (isset($data[AccountInfo::NAME]) && is_scalar($data[AccountInfo::NAME])) {
            $accountInfo->setName((string)$data[AccountInfo::NAME]);
        }

        if (isset($data[AccountInfo::SURNAME]) && is_scalar($data[AccountInfo::SURNAME])) {
            $accountInfo->setSurname((string)$data[AccountInfo::SURNAME]);
        }

        if (isset($data[AccountInfo::MAIL]) && is_scalar($data[AccountInfo::MAIL])) {
            $accountInfo->setMail((string)$data[AccountInfo::MAIL]);
        }

        $accountInfo->setPhoneNumber($this->preparePhoneNumber($data));
        $accountInfo->setClientAddress($this->prepareClientAddress($data));

        return $accountInfo;
    }

    public function preparePhoneNumber(array $data): PhoneNumber
    {
        /** @var PhoneNumber $phoneNumber */
        $phoneNumber = $this->phoneNumberFactory->create();
        if (isset($data[AccountInfo::PHONE_NUMBER]) && is_array($data[AccountInfo::PHONE_NUMBER])) {
            $phoneNumberData = (array)$data[AccountInfo::PHONE_NUMBER];
            if (isset($phoneNumberData[PhoneNumber::PHONE]) && is_scalar($phoneNumberData[PhoneNumber::PHONE])) {
                $phoneNumber->setPhone((string)$phoneNumberData[PhoneNumber::PHONE]);
            }

            if (isset($phoneNumberData[PhoneNumber::COUNTRY_PREFIX])
                && is_scalar($phoneNumberData[PhoneNumber::COUNTRY_PREFIX])
            ) {
                $phoneNumber->setCountryPrefix((string)$phoneNumberData[PhoneNumber::COUNTRY_PREFIX]);
            }
        }

        return $phoneNumber;
    }

    public function prepareClientAddress(array $data): ClientAddress
    {
        /** @var ClientAddress $clientAddress */
        $clientAddress = $this->clientAddressFactory->create();
        $addressDetailsData = [];

        if (isset($data[AccountInfo::CLIENT_ADDRESS]) && is_array($data[AccountInfo::CLIENT_ADDRESS])) {
            $clientAddressData = (array)$data[AccountInfo::CLIENT_ADDRESS];
            $this->appendClientAddressWithAddressData($clientAddress, $clientAddressData);
            if (isset($clientAddressData[ClientAddress::ADDRESS_DETAILS])
                && is_array($clientAddressData[ClientAddress::ADDRESS_DETAILS])
            ) {
                $addressDetailsData = (array)$clientAddressData[ClientAddress::ADDRESS_DETAILS];
            }
        }
        $addressDetails = $this->prepareAddressDetails($addressDetailsData);
        $clientAddress->setAddressDetails($addressDetails);

        return $clientAddress;
    }

    private function appendClientAddressWithAddressData(ClientAddress $clientAddress, array $clientAddressData): void
    {
        if (isset($clientAddressData[ClientAddress::ADDRESS])
            && is_scalar($clientAddressData[ClientAddress::ADDRESS])
        ) {
            $clientAddress->setAddress((string)$clientAddressData[ClientAddress::ADDRESS]);
        }

        if (isset($clientAddressData[ClientAddress::CITY])
            && is_scalar($clientAddressData[ClientAddress::CITY])
        ) {
            $clientAddress->setCity((string)$clientAddressData[ClientAddress::CITY]);
        }

        if (isset($clientAddressData[ClientAddress::POSTAL_CODE])
            && is_scalar($clientAddressData[ClientAddress::POSTAL_CODE])
        ) {
            $clientAddress->setPostalCode((string)$clientAddressData[ClientAddress::POSTAL_CODE]);
        }

        if (isset($clientAddressData[ClientAddress::COUNTRY_CODE])
            && is_scalar($clientAddressData[ClientAddress::COUNTRY_CODE])
        ) {
            $clientAddress->setCountryCode((string)$clientAddressData[ClientAddress::COUNTRY_CODE]);
        }
    }

    private function prepareAddressDetails(array $data): AddressDetails
    {
        /** @var AddressDetails $addressDetails */
        $addressDetails = $this->addressDetailsFactory->create();

        if (isset($data[AddressDetails::STREET]) && is_scalar($data[AddressDetails::STREET])) {
            $addressDetails->setStreet((string)$data[AddressDetails::STREET]);
        }

        if (isset($data[AddressDetails::BUILDING]) && is_scalar($data[AddressDetails::BUILDING])) {
            $addressDetails->setBuilding((string)$data[AddressDetails::BUILDING]);
        }

        if (isset($data[AddressDetails::FLAT]) && is_scalar($data[AddressDetails::FLAT])) {
            $addressDetails->setFlat((string)$data[AddressDetails::FLAT]);
        }

        return $addressDetails;
    }
}
