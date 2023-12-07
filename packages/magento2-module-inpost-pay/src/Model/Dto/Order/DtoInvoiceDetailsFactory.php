<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Dto\Order;

use InPost\InPostPay\Model\Dto\Order\InvoiceDetailsFactory;

class DtoInvoiceDetailsFactory
{
    public function __construct(
        private readonly InvoiceDetailsFactory $invoiceDetailsFactory
    ) {
    }

    public function create(array $data): InvoiceDetails
    {
        /** @var InvoiceDetails $invoiceDetails */
        $invoiceDetails = $this->invoiceDetailsFactory->create();

        $this->setLegalFormData($invoiceDetails, $data);
        $this->setCompanyData($invoiceDetails, $data);
        $this->setAddressData($invoiceDetails, $data);

        if (isset($data[InvoiceDetails::MAIL]) && is_scalar($data[InvoiceDetails::MAIL])) {
            $invoiceDetails->setMail((string)$data[InvoiceDetails::MAIL]);
        }

        if (isset($data[InvoiceDetails::REGISTRATION_DATA_EDITED])
            && is_scalar($data[InvoiceDetails::REGISTRATION_DATA_EDITED])
        ) {
            $invoiceDetails->setRegistrationDataEdited((string)$data[InvoiceDetails::REGISTRATION_DATA_EDITED]);
        }

        if (isset($data[InvoiceDetails::ADDITIONAL_INFORMATION])
            && is_scalar($data[InvoiceDetails::ADDITIONAL_INFORMATION])
        ) {
            $invoiceDetails->setAdditionalInformation((string)$data[InvoiceDetails::ADDITIONAL_INFORMATION]);
        }

        return $invoiceDetails;
    }

    private function setLegalFormData(InvoiceDetails $invoiceDetails, array $data): void
    {
        if (isset($data[InvoiceDetails::LEGAL_FORM]) && is_scalar($data[InvoiceDetails::LEGAL_FORM])) {
            $legalForm = (string)$data[InvoiceDetails::LEGAL_FORM];
            if ($legalForm === InvoiceDetails::LEGAL_FORM_PERSON
                || $legalForm === InvoiceDetails::LEGAL_FORM_COMPANY
            ) {
                $invoiceDetails->setLegalForm($legalForm);
            }
        }
    }

    /**
     * @param InvoiceDetails $invoiceDetails
     * @param array $data
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function setCompanyData(InvoiceDetails $invoiceDetails, array $data): void
    {
        if (isset($data[InvoiceDetails::TAX_ID_PREFIX]) && is_scalar($data[InvoiceDetails::TAX_ID_PREFIX])) {
            $invoiceDetails->setTaxIdPrefix((string)$data[InvoiceDetails::TAX_ID_PREFIX]);
        }

        if (isset($data[InvoiceDetails::TAX_ID]) && is_scalar($data[InvoiceDetails::TAX_ID])) {
            $invoiceDetails->setTaxId((string)$data[InvoiceDetails::TAX_ID]);
        }

        if (isset($data[InvoiceDetails::COMPANY_NAME]) && is_scalar($data[InvoiceDetails::COMPANY_NAME])) {
            $invoiceDetails->setCompanyName((string)$data[InvoiceDetails::COMPANY_NAME]);
        }

        if (isset($data[InvoiceDetails::NAME]) && is_scalar($data[InvoiceDetails::NAME])) {
            $invoiceDetails->setName((string)$data[InvoiceDetails::NAME]);
        }

        if (isset($data[InvoiceDetails::SURNAME]) && is_scalar($data[InvoiceDetails::SURNAME])) {
            $invoiceDetails->setSurname((string)$data[InvoiceDetails::SURNAME]);
        }
    }

    /**
     * @param InvoiceDetails $invoiceDetails
     * @param array $data
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function setAddressData(InvoiceDetails $invoiceDetails, array $data): void
    {
        if (isset($data[InvoiceDetails::COUNTRY_CODE]) && is_scalar($data[InvoiceDetails::COUNTRY_CODE])) {
            $invoiceDetails->setCountryCode((string)$data[InvoiceDetails::COUNTRY_CODE]);
        }

        if (isset($data[InvoiceDetails::CITY]) && is_scalar($data[InvoiceDetails::CITY])) {
            $invoiceDetails->setCity((string)$data[InvoiceDetails::CITY]);
        }

        if (isset($data[InvoiceDetails::STREET]) && is_scalar($data[InvoiceDetails::STREET])) {
            $invoiceDetails->setStreet((string)$data[InvoiceDetails::STREET]);
        }

        if (isset($data[InvoiceDetails::BUILDING]) && is_scalar($data[InvoiceDetails::BUILDING])) {
            $invoiceDetails->setBuilding((string)$data[InvoiceDetails::BUILDING]);
        }

        if (isset($data[InvoiceDetails::FLAT]) && is_scalar($data[InvoiceDetails::FLAT])) {
            $invoiceDetails->setFlat((string)$data[InvoiceDetails::FLAT]);
        }

        if (isset($data[InvoiceDetails::POSTAL_CODE]) && is_scalar($data[InvoiceDetails::POSTAL_CODE])) {
            $invoiceDetails->setPostalCode((string)$data[InvoiceDetails::POSTAL_CODE]);
        }
    }
}
