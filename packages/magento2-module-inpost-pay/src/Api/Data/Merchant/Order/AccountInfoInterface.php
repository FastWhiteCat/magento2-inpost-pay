<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Order;

use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;

interface AccountInfoInterface
{
    public const NAME = 'name';
    public const SURNAME = 'surname';
    public const PHONE_NUMBER = 'phone_number';
    public const MAIL = 'mail';
    public const CLIENT_ADDRESS = 'client_address';

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
    public function getSurname(): string;

    /**
     * @param string $surname
     * @return void
     */
    public function setSurname(string $surname): void;

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
     * @return string
     */
    public function getMail(): string;

    /**
     * @param string $mail
     * @return void
     */
    public function setMail(string $mail): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Order\ClientAddressInterface
     */
    public function getClientAddress(): ClientAddressInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Order\ClientAddressInterface $clientAddress
     * @return void
     */
    public function setClientAddress(ClientAddressInterface $clientAddress): void;
}
