<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\ConsentInterface;
use InPost\InPostPay\Enum\InPostConsentRequirementType;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class Consent extends DataObject implements ConsentInterface, ExtensibleDataInterface
{

    /**
     * @return string
     */
    public function getConsentId(): string
    {
        $consentId = $this->getData(self::CONSENT_ID);

        return is_scalar($consentId) ? (string)$consentId : '';
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
    public function getConsentLink(): string
    {
        $consentLink = $this->getData(self::CONSENT_LINK);

        return is_scalar($consentLink) ? (string)$consentLink : '';
    }

    /**
     * @param string $consentLink
     * @return void
     */
    public function setConsentLink(string $consentLink): void
    {
        $this->setData(self::CONSENT_LINK, $consentLink);
    }

    /**
     * @return string
     */
    public function getConsentDescription(): string
    {
        $consentDescription = $this->getData(self::CONSENT_DESCRIPTION);

        return is_scalar($consentDescription) ? (string)$consentDescription : '';
    }

    /**
     * @param string $consentDescription
     * @return void
     */
    public function setConsentDescription(string $consentDescription): void
    {
        $this->setData(self::CONSENT_DESCRIPTION, $consentDescription);
    }

    /**
     * @return string
     */
    public function getConsentVersion(): string
    {
        $consentVersion = $this->getData(self::CONSENT_VERSION);

        return is_scalar($consentVersion) ? (string)$consentVersion : '';
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
     * @return string
     */
    public function getRequirementType(): string
    {
        $requirementType = $this->getData(self::REQUIREMENT_TYPE);

        return is_scalar($requirementType) ? (string)$requirementType : InPostConsentRequirementType::OPTIONAL->value;
    }

    /**
     * @param string $requirementType
     * @return void
     */
    public function setRequirementType(string $requirementType): void
    {
        if ($requirementType === InPostConsentRequirementType::REQUIRED_ALWAYS->value
            || $requirementType === InPostConsentRequirementType::REQUIRED_ONCE->value
            || $requirementType === InPostConsentRequirementType::OPTIONAL->value
        ) {
            $this->setData(self::REQUIREMENT_TYPE, $requirementType);
        }
    }
}
