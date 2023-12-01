<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order;

use InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order\ClientAddressFactory;
use InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order\PhoneNumberFactory;

class AccountInfo
{
    public const NAME = 'name';
    public const SURNAME = 'surname';
    public const PHONE_NUMBER = 'phone_number';
    public const MAIL = 'mail';
    public const CLIENT_ADDRESS = 'client_address';

    private ?string $name;
    private ?string $surname;
    private ?PhoneNumber $phoneNumber;
    private ?string $mail;
    private ?ClientAddress $clientAddress;

    public function __construct(
        private readonly ClientAddressFactory $clientAddressFactory,
        private readonly PhoneNumberFactory $phoneNumberFactory
    ) {
    }

    public function getName(): string
    {
        return (string)$this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSurname(): string
    {
        return (string)$this->surname;
    }

    public function setSurname(string $surname): void
    {
        $this->surname = $surname;
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

    public function getMail(): string
    {
        return (string)$this->mail;
    }

    public function setMail(string $mail): void
    {
        $this->mail = $mail;
    }

    public function getClientAddress(): ClientAddress
    {
        if ($this->clientAddress === null) {
            $this->clientAddress = $this->clientAddressFactory->create();
        }

        return $this->clientAddress;
    }

    public function setClientAddress(ClientAddress $clientAddress): void
    {
        $this->clientAddress = $clientAddress;
    }
}
