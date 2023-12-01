<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Widget\Basket;

use InPost\InPostPay\Api\Widget\Basket\ConfirmationInterface;
use Magento\Framework\DataObject;

class Confirmation extends DataObject implements ConfirmationInterface
{
    public function getMessage(): string
    {
        return $this->getData('message');
    }

    public function getStatus(): ?string
    {
        return $this->getData('status');
    }

    public function getBrowserId(): ?string
    {
        return $this->getData('browser_id');
    }

    public function getBrowserTrusted(): ?bool
    {
        return $this->getData('browser_trusted');
    }

    public function getName(): ?string
    {
        return $this->getData('name');
    }

    public function getSurname(): ?string
    {
        return $this->getData('surname');
    }

    public function getMaskedPhoneNumber(): ?string
    {
        return $this->getData('masked_phone_number');
    }
}
