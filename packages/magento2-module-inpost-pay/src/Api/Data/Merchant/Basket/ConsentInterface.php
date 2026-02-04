<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket;

interface ConsentInterface
{
    public const CONSENT_ID = 'consent_id';
    public const CONSENT_LINK = 'consent_link';
    public const CONSENT_DESCRIPTION = 'consent_description';
    public const LABEL_LINK = 'label_link';
    public const ADDITIONAL_CONSENT_LINKS = 'additional_consent_links';
    public const CONSENT_VERSION = 'consent_version';
    public const REQUIREMENT_TYPE = 'requirement_type';

    /**
     * @return string
     */
    public function getConsentId(): string;

    /**
     * @param string $consentId
     * @return void
     */
    public function setConsentId(string $consentId): void;

    /**
     * @return string
     */
    public function getConsentLink(): string;

    /**
     * @param string $consentLink
     * @return void
     */
    public function setConsentLink(string $consentLink): void;

    /**
     * @return string
     */
    public function getConsentDescription(): string;

    /**
     * @param string $consentDescription
     * @return void
     */
    public function setConsentDescription(string $consentDescription): void;

    /**
     * @return string
     */
    public function getLabelLink(): string;

    /**
     * @param string $labelLink
     * @return void
     */
    public function setLabelLink(string $labelLink): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Consent\AdditionalConsentInterface[]
     */
    public function getAdditionalConsentLinks(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Consent\AdditionalConsentInterface[] $additionalConsentLinks
     * @return void
     */
    public function setAdditionalConsentLinks(array $additionalConsentLinks): void;

    /**
     * @return string
     */
    public function getConsentVersion(): string;

    /**
     * @param string $consentVersion
     * @return void
     */
    public function setConsentVersion(string $consentVersion): void;

    /**
     * @return string
     */
    public function getRequirementType(): string;

    /**
     * @param string $requirementType
     * @return void
     */
    public function setRequirementType(string $requirementType): void;
}
