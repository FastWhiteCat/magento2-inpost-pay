<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Order;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;
use InPost\InPostPay\Model\Dto\Order as DtoOrder;
use InPost\InPostPay\Model\Dto\Order\AccountInfo;
use InPost\InPostPay\Model\Dto\Order\ClientAddress;
use InPost\InPostPay\Model\Dto\Order\PhoneNumber;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class BillingInformationValidator implements OrderValidatorInterface
{

    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, DtoOrder $orderDto): void
    {
        if ($orderDto->getInvoiceDetails()) {
            //todo:: invoice data validation
        } else {
            $accountInfo = $orderDto->getAccountInfo();
            $this->validateName($accountInfo, $inPostPayQuote);
            $this->validateSurname($accountInfo, $inPostPayQuote);
            $this->validatePhoneNumber($accountInfo->getPhoneNumber(), $inPostPayQuote);
            $this->validateMail($accountInfo->getMail());
            $this->validateBillingAddress($accountInfo->getClientAddress());
        }
    }

    /**
     * @param AccountInfo $accountInfo
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @return void
     * @throws LocalizedException
     */
    private function validateName(AccountInfo $accountInfo, InPostPayQuoteInterface $inPostPayQuote): void
    {
        if ($accountInfo->getName() !== $inPostPayQuote->getName()) {
            throw new LocalizedException(
                __(
                    'Invalid name. Expected: %1 Received: %2',
                    $inPostPayQuote->getName(),
                    $accountInfo->getName()
                )
            );
        }
    }

    /**
     * @param AccountInfo $accountInfo
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @return void
     * @throws LocalizedException
     */
    private function validateSurname(AccountInfo $accountInfo, InPostPayQuoteInterface $inPostPayQuote): void
    {
        if ($accountInfo->getSurname() !== $inPostPayQuote->getSurname()) {
            throw new LocalizedException(
                __(
                    'Invalid surname. Expected: %1 Received: %2',
                    $inPostPayQuote->getSurname(),
                    $accountInfo->getSurname()
                )
            );
        }
    }

    /**
     * @param PhoneNumber $phoneNumber
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @return void
     * @throws LocalizedException
     */
    private function validatePhoneNumber(PhoneNumber $phoneNumber, InPostPayQuoteInterface $inPostPayQuote): void
    {
        $phoneNumberValue = trim($phoneNumber->getCountryPrefix()) . trim($phoneNumber->getPhone());
        if ($phoneNumberValue !== $inPostPayQuote->getPhoneNumber()) {
            throw new LocalizedException(
                __(
                    'Invalid phone number. Expected: %1 Received: %2',
                    $inPostPayQuote->getPhoneNumber(),
                    $phoneNumberValue
                )
            );
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
     * @param ClientAddress $clientAddress
     * @return void
     * @throws LocalizedException
     */
    private function validateBillingAddress(ClientAddress $clientAddress): void
    {
        $addressDetails = $clientAddress->getAddressDetails();
        if (empty($clientAddress->getCity())
            || empty($clientAddress->getCountryCode())
            || empty($clientAddress->getPostalCode())
            || empty($addressDetails->getStreet())
            || (empty($addressDetails->getBuilding()) && empty($addressDetails->getFlat()))
        ) {
            throw new LocalizedException(__('Incomplete billing address data.'));
        }
    }
}
