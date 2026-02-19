<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Order;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\ClientAddressInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;
use InPost\InPostPay\Enum\InPostInvoiceLegalForm;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class BillingInformationValidator implements OrderValidatorInterface
{
    /**
     * @param Quote $quote
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @param OrderInterface $inPostOrder
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, OrderInterface $inPostOrder): void
    {
        if ($inPostOrder->getInvoiceDetails()) {
            $this->validateInvoiceDetails($inPostOrder->getInvoiceDetails());
        } else {
            $accountInfo = $inPostOrder->getAccountInfo();
            $this->validatePhoneNumber($accountInfo->getPhoneNumber(), $inPostPayQuote);
            $this->validateMail($accountInfo->getMail());
            $this->validateBillingAddress($accountInfo->getClientAddress());
        }
    }

    /**
     * @param PhoneNumberInterface $phoneNumber
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @return void
     * @throws LocalizedException
     */
    private function validatePhoneNumber(
        PhoneNumberInterface $phoneNumber,
        InPostPayQuoteInterface $inPostPayQuote
    ): void {
        if ($inPostPayQuote->getPhone() !== $phoneNumber->getPhone()
            || $inPostPayQuote->getCountryPrefix() !== $phoneNumber->getCountryPrefix()
        ) {
            throw new LocalizedException(__('Invalid phone number.'));
        }
    }

    /**
     * @param string $mail
     * @return void
     * @throws LocalizedException
     */
    private function validateMail(string $mail): void
    {
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            throw new LocalizedException(__('Invalid mail format in: "%1"', $mail));
        }
    }

    /**
     * @param ClientAddressInterface $clientAddress
     * @return void
     * @throws LocalizedException
     */
    private function validateBillingAddress(ClientAddressInterface $clientAddress): void
    {
        if (empty($clientAddress->getCity())) {
            throw new LocalizedException(__('Incomplete billing address city data.'));
        }

        if (empty($clientAddress->getCountryCode())) {
            throw new LocalizedException(__('Incomplete billing address country data.'));
        }

        if (empty($clientAddress->getPostalCode())) {
            throw new LocalizedException(__('Incomplete billing address postal code data.'));
        }

        if (empty($clientAddress->getAddress())) {
            throw new LocalizedException(__('Incomplete billing address data.'));
        }
    }

    /**
     * @param InvoiceDetailsInterface $invoiceDetails
     * @return void
     * @throws LocalizedException
     */
    private function validateInvoiceDetails(InvoiceDetailsInterface $invoiceDetails): void
    {
        $this->validateLegalForm($invoiceDetails);

        if (empty($invoiceDetails->getCity())
            || empty($invoiceDetails->getCountryCode())
            || empty($invoiceDetails->getPostalCode())
            || empty($invoiceDetails->getStreet())
            || (empty($invoiceDetails->getBuilding()) && empty($invoiceDetails->getFlat()))
        ) {
            throw new LocalizedException(__('Incomplete invoice address data.'));
        }
    }

    /**
     * @param InvoiceDetailsInterface $invoiceDetails
     * @return void
     * @throws LocalizedException
     */
    private function validateLegalForm(InvoiceDetailsInterface $invoiceDetails): void
    {
        switch ($invoiceDetails->getLegalForm()) {
            case InPostInvoiceLegalForm::PERSON->value:
                if (empty($invoiceDetails->getName())) {
                    throw new LocalizedException(__('Empty Name.'));
                }
                break;
            case InPostInvoiceLegalForm::COMPANY->value:
                if (empty($invoiceDetails->getTaxId())) {
                    throw new LocalizedException(__('Empty Tax ID.'));
                }

                if (empty($invoiceDetails->getCompanyName())) {
                    throw new LocalizedException(__('Empty Company Name.'));
                }
                break;
            default:
                throw new LocalizedException(__('Invalid invoice legal form.'));
        }
    }
}
