<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Order;

use InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;

interface DeliveryInterface
{
    public const DELIVERY_TYPE = 'delivery_type';
    public const MAIL = 'mail';
    public const DIGITAL_DELIVERY_EMAIL = 'digital_delivery_email';
    public const PHONE_NUMBER = 'phone_number';
    public const DELIVERY_ADDRESS = 'delivery_address';
    public const DELIVERY_CODES = 'delivery_codes';
    public const DELIVERY_DATE = 'delivery_date';
    public const DELIVERY_OPTIONS = 'delivery_options';
    public const DELIVERY_PRICE = 'delivery_price';
    public const DELIVERY_POINT = 'delivery_point';
    public const COURIER_NOTE = 'courier_note';

    /**
     * @return string
     */
    public function getDeliveryType(): string;

    /**
     * @param string $deliveryType
     * @return void
     */
    public function setDeliveryType(string $deliveryType): void;

    /**
     * @return string[]
     */
    public function getDeliveryCodes(): array;

    /**
     * @param string[] $deliveryCodes
     * @return void
     */
    public function setDeliveryCodes(array $deliveryCodes): void;

    /**
     * @return string
     */
    public function getDeliveryDate(): string;

    /**
     * @param string $deliveryDate
     * @return void
     */
    public function setDeliveryDate(string $deliveryDate): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterface[]|null
     */
    public function getDeliveryOptions(): ?array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterface[]|null $deliveryOptions
     * @return void
     */
    public function setDeliveryOptions(?array $deliveryOptions): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface|null
     */
    public function getDeliveryPrice(): ?PriceInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface|null $deliveryPrice
     * @return void
     */
    public function setDeliveryPrice(?PriceInterface $deliveryPrice): void;

    /**
     * @return string
     */
    public function getMail(): string;

    /**
     * @param string $mail
     * @return void
     */
    public function setMail(string $mail): void;

    /**
     * @return string|null
     */
    public function getDigitalDeliveryEmail(): ?string;

    /**
     * @param string|null $digitalDeliveryEmail
     * @return void
     */
    public function setDigitalDeliveryEmail(?string $digitalDeliveryEmail): void;

    /**
     * @return string|null
     */
    public function getDeliveryPoint(): ?string;

    /**
     * @param string $deliveryPoint
     * @return void
     */
    public function setDeliveryPoint(string $deliveryPoint): void;

    /**
     * @return string
     */
    public function getCourierNote(): string;

    /**
     * @param string $courierNote
     * @return void
     */
    public function setCourierNote(string $courierNote): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface
     */
    public function getPhoneNumber(): PhoneNumberInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface $phoneNumber
     * @return void
     */
    public function setPhoneNumber(PhoneNumberInterface $phoneNumber): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Order\DeliveryAddressInterface
     */
    public function getDeliveryAddress(): DeliveryAddressInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\DeliveryAddressInterface $deliveryAddress
     * @return void
     */
    public function setDeliveryAddress(DeliveryAddressInterface $deliveryAddress): void;
}
