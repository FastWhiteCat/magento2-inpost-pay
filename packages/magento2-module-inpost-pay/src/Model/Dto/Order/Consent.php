<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Dto\Order;

class Consent
{
    public const CONSENT_ID = 'consent_id';
    public const CONSENT_VERSION = 'consent_version';
    public const IS_ACCEPTED = 'is_accepted';

    private ?int $consentId;
    private ?string $consentVersion;
    private ?bool $isAccepted;

    public function getConsentId(): int
    {
        return (int)$this->consentId;
    }

    public function setConsentId(int $consentId): void
    {
        $this->consentId = $consentId;
    }

    public function getConsentVersion(): string
    {
        return (string)$this->consentVersion;
    }

    public function setConsentVersion(string $consentVersion): void
    {
        $this->consentVersion = $consentVersion;
    }

    public function isAccepted(): bool
    {
        return (bool)$this->isAccepted;
    }

    public function setIsAccepted(bool $isAccepted): void
    {
        $this->isAccepted = $isAccepted;
    }
}
