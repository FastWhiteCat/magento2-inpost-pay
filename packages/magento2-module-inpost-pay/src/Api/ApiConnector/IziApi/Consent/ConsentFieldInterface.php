<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\IziApi\Consent;

interface ConsentFieldInterface
{
    public const CONSENTS = 'consents';
    public const CONSENT_ID = 'consent_id';
    public const CONSENT_LINK = 'consent_link';
    public const CONSENT_DESCRIPTION = 'consent_description';
    public const CONSENT_VERSION = 'consent_version';
    public const REQUIREMENT_TYPE = 'requirement_type';
}
