<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Refund;

use InPost\InPostPay\Api\Data\Merchant\Refund\AdditionalBusinessDataInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class AdditionalBusinessData extends DataObject implements AdditionalBusinessDataInterface, ExtensibleDataInterface
{
    public function getAdditionalData(): ?string
    {
        $additionalData = $this->getData(self::ADDITIONAL_DATA);

        return is_scalar($additionalData) ? (string)$additionalData : null;
    }

    public function setAdditionalData(?string $additionalData): AdditionalBusinessDataInterface
    {
        $this->setData(self::ADDITIONAL_DATA, $additionalData);

        return $this;
    }
}
