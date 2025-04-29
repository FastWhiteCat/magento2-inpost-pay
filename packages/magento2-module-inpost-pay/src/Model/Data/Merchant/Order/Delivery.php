<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Order;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryAddressInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryAddressInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface;
use InPost\InPostPay\Enum\InPostDeliveryType;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class Delivery extends DataObject implements DeliveryInterface, ExtensibleDataInterface
{
    /**
     * @param PhoneNumberInterfaceFactory $phoneNumberFactory
     * @param DeliveryAddressInterfaceFactory $deliveryAddressFactory
     * @param PriceInterfaceFactory $priceFactory
     * @param array $data
     */
    public function __construct(
        private readonly PhoneNumberInterfaceFactory $phoneNumberFactory,
        private readonly DeliveryAddressInterfaceFactory $deliveryAddressFactory,
        private readonly PriceInterfaceFactory $priceFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getDeliveryType(): string
    {
        $deliveryType = $this->getData(self::DELIVERY_TYPE);

        return (is_scalar($deliveryType)) ? (string)$deliveryType : InPostDeliveryType::COURIER->name;
    }

    /**
     * @param string $deliveryType
     * @return void
     */
    public function setDeliveryType(string $deliveryType): void
    {
        $this->setData(self::DELIVERY_TYPE, $deliveryType);
    }

    /**
     * @return string[]
     */
    public function getDeliveryCodes(): array
    {
        $deliveryCodes = $this->getData(self::DELIVERY_CODES);

        if (is_array($deliveryCodes)) {
            asort($deliveryCodes);

            return $deliveryCodes;
        }

        return [];
    }

    /**
     * @param string[] $deliveryCodes
     * @return void
     */
    public function setDeliveryCodes(array $deliveryCodes): void
    {
        $this->setData(self::DELIVERY_CODES, $deliveryCodes);
    }

    /**
     * @return string
     */
    public function getDeliveryDate(): string
    {
        $deliveryDate = $this->getData(self::DELIVERY_DATE);

        return (is_scalar($deliveryDate)) ? (string)$deliveryDate : '';
    }

    /**
     * @param string $deliveryDate
     * @return void
     */
    public function setDeliveryDate(string $deliveryDate): void
    {
        $this->setData(self::DELIVERY_DATE, $deliveryDate);
    }

    /**
     * @return DeliveryOptionInterface[]|null
     */
    public function getDeliveryOptions(): ?array
    {
        $deliveryOptions = $this->getData(self::DELIVERY_OPTIONS);

        if (is_array($deliveryOptions)) {
            return $deliveryOptions;
        }

        return [];
    }

    /**
     * @param DeliveryOptionInterface[]|null $deliveryOptions
     * @return void
     */
    public function setDeliveryOptions(?array $deliveryOptions): void
    {
        $this->setData(self::DELIVERY_OPTIONS, $deliveryOptions);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface|null
     */
    public function getDeliveryPrice(): ?PriceInterface
    {
        $deliveryPrice = $this->getData(self::DELIVERY_PRICE);

        if ($deliveryPrice instanceof PriceInterface) {
            return $deliveryPrice;
        }

        return $this->priceFactory->create();
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface|null $deliveryPrice
     * @return void
     */
    public function setDeliveryPrice(?PriceInterface $deliveryPrice): void
    {
        $this->setData(self::DELIVERY_PRICE, $deliveryPrice);
    }

    /**
     * @return string
     */
    public function getMail(): string
    {
        $mail = $this->getData(self::MAIL);

        return (is_scalar($mail)) ? (string)$mail : '';
    }

    /**
     * @param string $mail
     * @return void
     */
    public function setMail(string $mail): void
    {
        $this->setData(self::MAIL, $mail);
    }

    /**
     * @return string|null
     */
    public function getDigitalDeliveryEmail(): ?string
    {
        $digitalDeliveryEmail = null;

        if ($this->hasData(self::DIGITAL_DELIVERY_EMAIL)) {
            $digitalDeliveryEmail = $this->getData(self::DIGITAL_DELIVERY_EMAIL);
        }

        $digitalDeliveryEmail = $digitalDeliveryEmail ?? null;

        return ($digitalDeliveryEmail && is_scalar($digitalDeliveryEmail)) ? (string)$digitalDeliveryEmail : null;
    }

    /**
     * @param string|null $digitalDeliveryEmail
     * @return void
     */
    public function setDigitalDeliveryEmail(?string $digitalDeliveryEmail): void
    {
        $this->setData(self::DIGITAL_DELIVERY_EMAIL, $digitalDeliveryEmail);
    }

    /**
     * @return string|null
     */
    public function getDeliveryPoint(): ?string
    {
        $deliveryPoint = $this->getData(self::DELIVERY_POINT);

        return ($deliveryPoint && is_scalar($deliveryPoint)) ? (string)$deliveryPoint : null;
    }

    /**
     * @param string $deliveryPoint
     * @return void
     */
    public function setDeliveryPoint(string $deliveryPoint): void
    {
        $this->setData(self::DELIVERY_POINT, $deliveryPoint);
    }

    /**
     * @return string
     */
    public function getCourierNote(): string
    {
        $courierNote = $this->getData(self::COURIER_NOTE);

        return (is_scalar($courierNote)) ? (string)$courierNote : '';
    }

    /**
     * @param string $courierNote
     * @return void
     */
    public function setCourierNote(string $courierNote): void
    {
        $this->setData(self::COURIER_NOTE, $courierNote);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface
     */
    public function getPhoneNumber(): PhoneNumberInterface
    {
        $phoneNumber = $this->getData(self::PHONE_NUMBER);

        if ($phoneNumber instanceof PhoneNumberInterface) {
            return $phoneNumber;
        }

        return $this->phoneNumberFactory->create();
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface $phoneNumber
     * @return void
     */
    public function setPhoneNumber(PhoneNumberInterface $phoneNumber): void
    {
        $this->setData(self::PHONE_NUMBER, $phoneNumber);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Order\DeliveryAddressInterface
     */
    public function getDeliveryAddress(): DeliveryAddressInterface
    {
        $deliveryAddress = $this->getData(self::DELIVERY_ADDRESS);

        if ($deliveryAddress instanceof DeliveryAddressInterface) {
            return $deliveryAddress;
        }

        return $this->deliveryAddressFactory->create();
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\DeliveryAddressInterface $deliveryAddress
     * @return void
     */
    public function setDeliveryAddress(DeliveryAddressInterface $deliveryAddress): void
    {
        $this->setData(self::DELIVERY_ADDRESS, $deliveryAddress);
    }
}
