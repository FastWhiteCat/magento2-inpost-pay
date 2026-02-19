<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Order;

interface InvoiceDetailsInterface
{
    public const LEGAL_FORM = 'legal_form';
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

    /**
     * @return string
     */
    public function getLegalForm(): string;

    /**
     * @param string $legalForm
     * @return void
     */
    public function setLegalForm(string $legalForm): void;

    /**
     * @return string
     */
    public function getCountryCode(): string;

    /**
     * @param string $countryCode
     * @return void
     */
    public function setCountryCode(string $countryCode): void;

    /**
     * @return string
     */
    public function getTaxIdPrefix(): string;

    /**
     * @param string $taxIdPrefix
     * @return void
     */
    public function setTaxIdPrefix(string $taxIdPrefix): void;

    /**
     * @return string
     */
    public function getTaxId(): string;

    /**
     * @param string $taxId
     * @return void
     */
    public function setTaxId(string $taxId): void;

    /**
     * @return string
     */
    public function getCompanyName(): string;

    /**
     * @param string $companyName
     * @return void
     */
    public function setCompanyName(string $companyName): void;

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
     * @return string
     */
    public function getCity(): string;

    /**
     * @param string $city
     * @return void
     */
    public function setCity(string $city): void;

    /**
     * @return string|null
     */
    public function getStreet(): ?string;

    /**
     * @param string|null $street
     * @return void
     */
    public function setStreet(?string $street): void;

    /**
     * @return string|null
     */
    public function getBuilding(): ?string;

    /**
     * @param string|null $building
     * @return void
     */
    public function setBuilding(?string $building): void;

    /**
     * @return string|null
     */
    public function getFlat(): ?string;

    /**
     * @param string|null $flat
     * @return void
     */
    public function setFlat(?string $flat): void;

    /**
     * @return string
     */
    public function getPostalCode(): string;

    /**
     * @param string $postalCode
     * @return void
     */
    public function setPostalCode(string $postalCode): void;

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
     * @return string
     */
    public function getRegistrationDataEdited(): string;

    /**
     * @param string $registrationDataEdited
     * @return void
     */
    public function setRegistrationDataEdited(string $registrationDataEdited): void;

    /**
     * @return string
     */
    public function getAdditionalInformation(): string;

    /**
     * @param string $additionalInformation
     * @return void
     */
    public function setAdditionalInformation(string $additionalInformation): void;
}
