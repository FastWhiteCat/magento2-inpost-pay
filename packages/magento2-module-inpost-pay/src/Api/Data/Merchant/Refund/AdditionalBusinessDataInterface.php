<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Refund;

interface AdditionalBusinessDataInterface
{
    public const ADDITIONAL_DATA = 'additional_data';

    /**
     * @return string
     */
    public function getAdditionalData(): ?string;

    /**
     * @param null|string $additionalData
     *
     * @return AdditionalBusinessDataInterface
     */
    public function setAdditionalData(?string $additionalData): self;
}
