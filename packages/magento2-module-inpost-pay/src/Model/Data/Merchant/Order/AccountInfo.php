<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Order;

use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\ClientAddressInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\ClientAddressInterfaceFactory;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class AccountInfo extends DataObject implements AccountInfoInterface, ExtensibleDataInterface
{
    /**
     * @param PhoneNumberInterfaceFactory $phoneNumberFactory
     * @param ClientAddressInterfaceFactory $clientAddressFactory
     * @param array $data
     */
    public function __construct(
        private readonly PhoneNumberInterfaceFactory $phoneNumberFactory,
        private readonly ClientAddressInterfaceFactory $clientAddressFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        $name = $this->getData(self::NAME);

        return (is_scalar($name)) ? (string)$name : '';
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->setData(self::NAME, $name);
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        $surname = $this->getData(self::SURNAME);

        return (is_scalar($surname)) ? (string)$surname : '';
    }

    /**
     * @param string $surname
     * @return void
     */
    public function setSurname(string $surname): void
    {
        $this->setData(self::SURNAME, $surname);
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
     * @return \InPost\InPostPay\Api\Data\Merchant\Order\ClientAddressInterface
     */
    public function getClientAddress(): ClientAddressInterface
    {
        $clientAddress = $this->getData(self::CLIENT_ADDRESS);

        if ($clientAddress instanceof ClientAddressInterface) {
            return $clientAddress;
        }

        return $this->clientAddressFactory->create();
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\ClientAddressInterface $clientAddress
     * @return void
     */
    public function setClientAddress(ClientAddressInterface $clientAddress): void
    {
        $this->setData(self::CLIENT_ADDRESS, $clientAddress);
    }
}
