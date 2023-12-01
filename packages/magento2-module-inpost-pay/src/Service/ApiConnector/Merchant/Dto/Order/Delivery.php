<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order;

use InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order\PhoneNumberFactory;
use InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order\DeliveryAddressFactory;

class Delivery
{
    public const DELIVERY_TYPE = 'delivery_type';
    public const MAIL = 'mail';
    public const PHONE_NUMBER = 'phone_number';
    public const DELIVERY_ADDRESS = 'delivery_address';

    private ?string $deliveryType;
    private ?string $mail;
    private ?PhoneNumber $phoneNumber;
    private ?DeliveryAddress $deliveryAddress;

    public function __construct(
        private readonly DeliveryAddressFactory $deliveryAddressFactory,
        private readonly PhoneNumberFactory $phoneNumberFactory
    ) {
    }

    public function getDeliveryType(): string
    {
        return (string)$this->deliveryType;
    }

    public function setDeliveryType(string $deliveryType): void
    {
        $this->deliveryType = $deliveryType;
    }

    public function getMail(): string
    {
        return (string)$this->mail;
    }

    public function setMail(string $mail): void
    {
        $this->mail = $mail;
    }

    public function getPhoneNumber(): PhoneNumber
    {
        if ($this->phoneNumber === null) {
            $this->phoneNumber = $this->phoneNumberFactory->create();
        }

        return $this->phoneNumber;
    }

    public function setPhoneNumber(PhoneNumber $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getDeliveryAddress(): DeliveryAddress
    {
        if ($this->deliveryAddress === null) {
            $this->deliveryAddress = $this->deliveryAddressFactory->create();
        }

        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(DeliveryAddress $deliveryAddress): void
    {
        $this->deliveryAddress = $deliveryAddress;
    }
}
