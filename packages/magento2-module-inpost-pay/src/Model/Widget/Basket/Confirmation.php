<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Widget\Basket;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Widget\Basket\ConfirmationInterface;
use Magento\Framework\DataObject;

class Confirmation extends DataObject implements ConfirmationInterface
{
    public function getMessage(): string
    {
        $message = $this->getData(ConfirmationInterface::MESSAGE);

        return (is_scalar($message)) ? (string)$message : '';
    }

    public function getStatus(): ?string
    {
        $data = $this->getData(InPostPayQuoteInterface::STATUS);

        return is_string($data) ? $data : null;
    }

    public function getBrowserId(): ?string
    {
        $data = $this->getData(InPostPayQuoteInterface::BROWSER_ID);

        return is_string($data) ? $data : null;
    }

    public function getBrowserTrusted(): ?bool
    {
        $data = $this->getData(InPostPayQuoteInterface::BROWSER_TRUSTED);

        return is_bool($data) ? $data : null;
    }

    public function getName(): ?string
    {
        $data = $this->getData(InPostPayQuoteInterface::NAME);

        return is_string($data) ? $data : null;
    }

    public function getSurname(): ?string
    {
        $data = $this->getData(InPostPayQuoteInterface::SURNAME);

        return is_string($data) ? $data : null;
    }

    public function getMaskedPhoneNumber(): ?string
    {
        $data = $this->getData(InPostPayQuoteInterface::MASKED_PHONE_NUMBER);

        return is_string($data) ? $data : null;
    }
}
