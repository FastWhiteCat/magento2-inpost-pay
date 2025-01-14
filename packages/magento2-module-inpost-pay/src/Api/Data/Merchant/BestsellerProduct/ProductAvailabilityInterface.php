<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\BestsellerProduct;

interface ProductAvailabilityInterface
{
    public const START_DATE = 'start_date';
    public const END_DATE = 'end_date';

    /**
     * @return string|null
     */
    public function getStartDate(): ?string;

    /**
     * @param string|null $startDate
     * @return void
     */
    public function setStartDate(?string $startDate): void;

    /**
     * @return string|null
     */
    public function getEndDate(): ?string;

    /**
     * @param string|null $endDate
     * @return void
     */
    public function setEndDate(?string $endDate): void;
}
