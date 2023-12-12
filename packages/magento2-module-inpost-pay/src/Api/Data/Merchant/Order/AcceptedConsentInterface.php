<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Order;

interface AcceptedConsentInterface
{
    public const CONSENT_ID = 'consent_id';
    public const CONSENT_VERSION = 'consent_version';
    public const IS_ACCEPTED = 'is_accepted';
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
    public function getConsentVersion(): string;

    /**
     * @param string $consentVersion
     * @return void
     */
    public function setConsentVersion(string $consentVersion): void;

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsAccepted(): bool;

    /**
     * @param bool $isAccepted
     * @return void
     */
    public function setIsAccepted(bool $isAccepted): void;
}
