<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket\Consent;

use InPost\InPostPay\Api\Data\Merchant\Basket\Consent\AdditionalConsentInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class AdditionalConsent extends DataObject implements AdditionalConsentInterface, ExtensibleDataInterface
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
    public function getLabelLink(): string
    {
        $labelLink = $this->getData(self::LABEL_LINK);

        return is_scalar($labelLink) ? (string)$labelLink : '';
    }

    /**
     * @param string $labelLink
     * @return void
     */
    public function setLabelLink(string $labelLink): void
    {
        $this->setData(self::LABEL_LINK, $labelLink);
    }
}
