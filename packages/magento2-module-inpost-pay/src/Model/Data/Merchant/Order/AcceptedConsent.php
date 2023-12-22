<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Order;

use InPost\InPostPay\Api\Data\Merchant\Order\AcceptedConsentInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class AcceptedConsent extends DataObject implements AcceptedConsentInterface, ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getConsentId(): string
    {
        $consentId = $this->getData(self::CONSENT_ID);

        return (is_scalar($consentId)) ? (string)$consentId : '';
    }

    /**
     * @param string $consentId
     * @return void
     */
    public function setConsentId(string $consentId): void
    {
        $this->setData(self::CONSENT_ID, $consentId);
    }

    /**
     * @return string
     */
    public function getConsentVersion(): string
    {
        $consentVersion = $this->getData(self::CONSENT_VERSION);

        return (is_scalar($consentVersion)) ? (string)$consentVersion : '';
    }

    /**
     * @param string $consentVersion
     * @return void
     */
    public function setConsentVersion(string $consentVersion): void
    {
        $this->setData(self::CONSENT_VERSION, $consentVersion);
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsAccepted(): bool
    {
        $isAccepted = $this->getData(self::IS_ACCEPTED);

        return (is_bool($isAccepted)) ? $isAccepted : false;
    }

    /**
     * @param bool $isAccepted
     * @return void
     */
    public function setIsAccepted(bool $isAccepted): void
    {
        $this->setData(self::IS_ACCEPTED, $isAccepted);
    }
}
