<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Dto\Order;

use InPost\InPostPay\Model\Dto\Order\DeliveryAddressFactory;
use InPost\InPostPay\Model\Dto\Order\PhoneNumberFactory;

class Delivery
{
    public const DELIVERY_TYPE = 'delivery_type';
    public const MAIL = 'mail';
    public const PHONE_NUMBER = 'phone_number';
    public const DELIVERY_ADDRESS = 'delivery_address';
    public const DELIVERY_CODES = 'delivery_codes';
    public const DELIVERY_POINT = 'delivery_point';
    public const COURIER_NOTE = 'courier_note';

    private ?string $deliveryType;
    private ?array $deliveryCodes;
    private ?string $mail;
    private ?string $deliveryPoint;
    private ?string $courierNote;
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

    public function getDeliveryCodes(): array
    {
        return (array)$this->deliveryCodes;
    }

    public function setDeliveryCodes(array $deliveryCodes): void
    {
        $this->deliveryCodes = $deliveryCodes;
    }

    public function getMail(): string
    {
        return (string)$this->mail;
    }

    public function setMail(string $mail): void
    {
        $this->mail = $mail;
    }

    public function getDeliveryPoint(): ?string
    {
        return $this->deliveryPoint;
    }

    public function setDeliveryPoint(string $deliveryPoint): void
    {
        $this->deliveryPoint = $deliveryPoint;
    }

    public function getCourierNote(): string
    {
        return (string)$this->courierNote;
    }

    public function setCourierNote(string $courierNote): void
    {
        $this->courierNote = $courierNote;
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
