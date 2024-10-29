<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Order;

use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface;
use InPost\InPostPay\Enum\InPostInvoiceLegalForm;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class InvoiceDetails extends DataObject implements InvoiceDetailsInterface, ExtensibleDataInterface
{
    private const DEFAULT_COUNTRY_CODE = 'PL';

    /**
     * @return string
     */
    public function getLegalForm(): string
    {
        $legalForm = $this->getData(self::LEGAL_FORM);

        return (is_scalar($legalForm)) ? (string)$legalForm : InPostInvoiceLegalForm::PERSON->value;
    }

    /**
     * @param string $legalForm
     * @return void
     */
    public function setLegalForm(string $legalForm): void
    {
        $this->setData(self::LEGAL_FORM, $legalForm);
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        $countryCode = $this->getData(self::COUNTRY_CODE);

        return (is_scalar($countryCode)) ? (string)$countryCode : self::DEFAULT_COUNTRY_CODE;
    }

    /**
     * @param string $countryCode
     * @return void
     */
    public function setCountryCode(string $countryCode): void
    {
        $this->setData(self::COUNTRY_CODE, $countryCode);
    }

    /**
     * @return string
     */
    public function getTaxIdPrefix(): string
    {
        $taxIdPrefix = $this->getData(self::TAX_ID_PREFIX);

        return (is_scalar($taxIdPrefix)) ? (string)$taxIdPrefix : '';
    }

    /**
     * @param string $taxIdPrefix
     * @return void
     */
    public function setTaxIdPrefix(string $taxIdPrefix): void
    {
        $this->setData(self::TAX_ID_PREFIX, $taxIdPrefix);
    }

    /**
     * @return string
     */
    public function getTaxId(): string
    {
        $taxId = $this->getData(self::TAX_ID);

        return (is_scalar($taxId)) ? (string)$taxId : '';
    }

    /**
     * @param string $taxId
     * @return void
     */
    public function setTaxId(string $taxId): void
    {
        $this->setData(self::TAX_ID, $taxId);
    }

    /**
     * @return string
     */
    public function getCompanyName(): string
    {
        $companyName = $this->getData(self::COMPANY_NAME);

        return (is_scalar($companyName)) ? (string)$companyName : '';
    }

    /**
     * @param string $companyName
     * @return void
     */
    public function setCompanyName(string $companyName): void
    {
        $this->setData(self::COMPANY_NAME, $companyName);
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
     * @return string
     */
    public function getCity(): string
    {
        $city = $this->getData(self::CITY);

        return (is_scalar($city)) ? (string)$city : '';
    }

    /**
     * @param string $city
     * @return void
     */
    public function setCity(string $city): void
    {
        $this->setData(self::CITY, $city);
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        $street = $this->getData(self::STREET);

        return (is_scalar($street)) ? (string)$street : '';
    }

    /**
     * @param string|null $street
     * @return void
     */
    public function setStreet(?string $street): void
    {
        $this->setData(self::STREET, $street);
    }

    /**
     * @return string|null
     */
    public function getBuilding(): ?string
    {
        $building = $this->getData(self::BUILDING);

        return (is_scalar($building)) ? (string)$building : '';
    }

    /**
     * @param string|null $building
     * @return void
     */
    public function setBuilding(?string $building): void
    {
        $this->setData(self::BUILDING, $building);
    }

    /**
     * @return string|null
     */
    public function getFlat(): ?string
    {
        $flat = $this->getData(self::FLAT);

        return (is_scalar($flat)) ? (string)$flat : '';
    }

    /**
     * @param string|null $flat
     * @return void
     */
    public function setFlat(?string $flat): void
    {
        $this->setData(self::FLAT, $flat);
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        $postalCode = $this->getData(self::POSTAL_CODE);

        return (is_scalar($postalCode)) ? (string)$postalCode : '';
    }

    /**
     * @param string $postalCode
     * @return void
     */
    public function setPostalCode(string $postalCode): void
    {
        $this->setData(self::POSTAL_CODE, $postalCode);
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
     * @return string
     */
    public function getRegistrationDataEdited(): string
    {
        $registrationDataEdited = $this->getData(self::REGISTRATION_DATA_EDITED);

        return (is_scalar($registrationDataEdited)) ? (string)$registrationDataEdited : '';
    }

    /**
     * @param string $registrationDataEdited
     * @return void
     */
    public function setRegistrationDataEdited(string $registrationDataEdited): void
    {
        $this->setData(self::REGISTRATION_DATA_EDITED, $registrationDataEdited);
    }

    /**
     * @return string
     */
    public function getAdditionalInformation(): string
    {
        $additionalInformation = $this->getData(self::ADDITIONAL_INFORMATION);

        return (is_scalar($additionalInformation)) ? (string)$additionalInformation : '';
    }

    /**
     * @param string $additionalInformation
     * @return void
     */
    public function setAdditionalInformation(string $additionalInformation): void
    {
        $this->setData(self::ADDITIONAL_INFORMATION, $additionalInformation);
    }
}
