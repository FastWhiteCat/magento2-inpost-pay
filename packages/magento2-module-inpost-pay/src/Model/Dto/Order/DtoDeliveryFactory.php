<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Dto\Order;

use InPost\InPostPay\Model\Dto\Order\AddressDetailsFactory;
use InPost\InPostPay\Model\Dto\Order\DeliveryAddressFactory;
use InPost\InPostPay\Model\Dto\Order\DeliveryFactory;
use InPost\InPostPay\Model\Dto\Order\PhoneNumberFactory;

class DtoDeliveryFactory
{
    public function __construct(
        private readonly DeliveryFactory $deliveryFactory,
        private readonly PhoneNumberFactory $phoneNumberFactory,
        private readonly DeliveryAddressFactory $deliveryAddressFactory,
        private readonly AddressDetailsFactory $addressDetailsFactory
    ) {
    }

    public function create(array $data): Delivery
    {
        /** @var Delivery $delivery */
        $delivery = $this->deliveryFactory->create();

        if (isset($data[Delivery::DELIVERY_TYPE]) && is_scalar($data[Delivery::DELIVERY_TYPE])) {
            $delivery->setDeliveryType((string)$data[Delivery::DELIVERY_TYPE]);
        }

        if (isset($data[Delivery::DELIVERY_CODES]) && is_array($data[Delivery::DELIVERY_CODES])) {
            $deliveryCodes = [];
            foreach ($data[Delivery::DELIVERY_CODES] as $deliveryCode) {
                if (is_scalar($deliveryCode)) {
                    $deliveryCodes[] = (string)$deliveryCode;
                }
            }
            asort($deliveryCodes);
            $delivery->setDeliveryCodes($deliveryCodes);
        }

        if (isset($data[Delivery::MAIL]) && is_scalar($data[Delivery::MAIL])) {
            $delivery->setMail((string)$data[Delivery::MAIL]);
        }

        if (isset($data[Delivery::DELIVERY_POINT]) && is_scalar($data[Delivery::DELIVERY_POINT])) {
            $delivery->setDeliveryPoint((string)$data[Delivery::DELIVERY_POINT]);
        }

        if (isset($data[Delivery::COURIER_NOTE]) && is_scalar($data[Delivery::COURIER_NOTE])) {
            $delivery->setCourierNote((string)$data[Delivery::COURIER_NOTE]);
        }

        $delivery->setPhoneNumber($this->preparePhoneNumber($data));
        $delivery->setDeliveryAddress($this->prepareDeliveryAddress($data));


        return $delivery;
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

    public function prepareDeliveryAddress(array $data): DeliveryAddress
    {
        /** @var DeliveryAddress $deliveryAddress */
        $deliveryAddress = $this->deliveryAddressFactory->create();
        $addressDetailsData = [];

        if (isset($data[Delivery::DELIVERY_ADDRESS]) && is_array($data[Delivery::DELIVERY_ADDRESS])) {
            $deliveryAddressData = (array)$data[Delivery::DELIVERY_ADDRESS];
            if (isset($deliveryAddressData[DeliveryAddress::NAME])
                && is_scalar($deliveryAddressData[DeliveryAddress::NAME])
            ) {
                $deliveryAddress->setName((string)$deliveryAddressData[DeliveryAddress::NAME]);
            }

            if (isset($deliveryAddressData[DeliveryAddress::COUNTRY_CODE])
                && is_scalar($deliveryAddressData[DeliveryAddress::COUNTRY_CODE])
            ) {
                $deliveryAddress->setCountryCode((string)$deliveryAddressData[DeliveryAddress::COUNTRY_CODE]);
            }

            if (isset($deliveryAddressData[DeliveryAddress::ADDRESS])
                && is_scalar($deliveryAddressData[DeliveryAddress::ADDRESS])
            ) {
                $deliveryAddress->setAddress((string)$deliveryAddressData[DeliveryAddress::ADDRESS]);
            }

            if (isset($deliveryAddressData[DeliveryAddress::CITY])
                && is_scalar($deliveryAddressData[DeliveryAddress::CITY])
            ) {
                $deliveryAddress->setCity((string)$deliveryAddressData[DeliveryAddress::CITY]);
            }

            if (isset($deliveryAddressData[DeliveryAddress::POSTAL_CODE])
                && is_scalar($deliveryAddressData[DeliveryAddress::POSTAL_CODE])
            ) {
                $deliveryAddress->setPostalCode((string)$deliveryAddressData[DeliveryAddress::POSTAL_CODE]);
            }

            if (isset($deliveryAddressData[DeliveryAddress::ADDRESS_DETAILS])
                && is_array($deliveryAddressData[DeliveryAddress::ADDRESS_DETAILS])
            ) {
                $addressDetailsData = (array)$deliveryAddressData[DeliveryAddress::ADDRESS_DETAILS];
            }
        }
        $addressDetails = $this->prepareAddressDetails($addressDetailsData);
        $deliveryAddress->setAddressDetails($addressDetails);

        return $deliveryAddress;
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
