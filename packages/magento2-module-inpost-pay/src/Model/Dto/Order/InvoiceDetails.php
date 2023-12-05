<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Dto\Order;

use InPost\InPostPay\Model\Dto\Order\BasketPriceFactory;

class InvoiceDetails
{
    public const LEGAL_FORM = 'legal_form';

    public const LEGAL_FORM_PERSON = 'PERSON';
    public const LEGAL_FORM_COMPANY = 'COMPANY';
    public const COUNTRY_CODE = 'country_code';
    public const TAX_ID_PREFIX = 'tax_id_prefix';
    public const TAX_ID = 'tax_id';
    public const COMPANY_NAME = 'company_name';
    public const NAME = 'name';
    public const SURNAME = 'surname';
    public const CITY = 'city';
    public const STREET = 'street';
    public const BUILDING = 'building';
    public const FLAT = 'flat';
    public const POSTAL_CODE = 'postal_code';
    public const MAIL = 'mail';
    public const REGISTRATION_DATA_EDITED = 'registration_data_edited';
    public const ADDITIONAL_INFORMATION = 'additional_information';

    private ?string $legalForm;
    private ?string $countryCode;
    private ?string $taxIdPrefix;
    private ?string $taxId;
    private ?string $companyName;
    private ?string $name;
    private ?string $surname;
    private ?string $city;
    private ?string $street;
    private ?string $building;
    private ?string $flat;
    private ?string $postalCode;
    private ?string $mail;
    private ?string $registrationDataEdited;
    private ?string $additionalInformation;

    public function getLegalForm(): string
    {
        return $this->legalForm ?? self::LEGAL_FORM_PERSON;
    }

    public function setLegalForm(string $legalForm): void
    {
        $this->legalForm = $legalForm;
    }

    public function getCountryCode(): string
    {
        return (string)$this->countryCode;
    }

    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    public function getTaxIdPrefix(): string
    {
        return (string)$this->taxIdPrefix;
    }

    public function setTaxIdPrefix(string $taxIdPrefix): void
    {
        $this->taxIdPrefix = $taxIdPrefix;
    }

    public function getTaxId(): string
    {
        return (string)$this->taxId;
    }

    public function setTaxId(string $taxId): void
    {
        $this->taxId = $taxId;
    }

    public function getCompanyName(): string
    {
        return (string)$this->companyName;
    }

    public function setCompanyName(string $companyName): void
    {
        $this->companyName = $companyName;
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

    public function getCity(): string
    {
        return (string)$this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getStreet(): string
    {
        return (string)$this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getBuilding(): string
    {
        return (string)$this->building;
    }

    public function setBuilding(string $building): void
    {
        $this->building = $building;
    }

    public function getFlat(): string
    {
        return (string)$this->flat;
    }

    public function setFlat(string $flat): void
    {
        $this->flat = $flat;
    }

    public function getPostalCode(): string
    {
        return (string)$this->postalCode;
    }

    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getMail(): string
    {
        return (string)$this->mail;
    }

    public function setMail(string $mail): void
    {
        $this->mail = $mail;
    }

    public function getRegistrationDataEdited(): string
    {
        return (string)$this->registrationDataEdited;
    }

    public function setRegistrationDataEdited(string $registrationDataEdited): void
    {
        $this->registrationDataEdited = $registrationDataEdited;
    }

    public function getAdditionalInformation(): string
    {
        return (string)$this->additionalInformation;
    }

    public function setAdditionalInformation(string $additionalInformation): void
    {
        $this->additionalInformation = $additionalInformation;
    }
}
