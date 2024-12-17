<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket\Consent;

interface AdditionalConsentInterface
{
    public const ID = 'id';
    public const CONSENT_LINK = 'consent_link';
    public const LABEL_LINK = 'label_link';

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @param string $id
     * @return void
     */
    public function setId(string $id): void;

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
    public function getLabelLink(): string;

    /**
     * @param string $labelLink
     * @return void
     */
    public function setLabelLink(string $labelLink): void;
}
