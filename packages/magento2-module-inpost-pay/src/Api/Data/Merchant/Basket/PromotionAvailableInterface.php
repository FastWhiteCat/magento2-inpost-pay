<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailable\DetailsInterface;

interface PromotionAvailableInterface
{
    public const TYPE = 'type';
    public const PROMO_CODE_VALUE = 'promo_code_value';
    public const DESCRIPTION = 'description';
    public const START_DATE = 'start_date';
    public const END_DATE = 'end_date';
    public const PRIORITY = 'priority';
    public const DETAILS = 'details';

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     * @return void
     */
    public function setType(string $type): void;

    /**
     * @return string
     */
    public function getPromoCodeValue(): string;

    /**
     * @param string $promoCodeValue
     * @return void
     */
    public function setPromoCodeValue(string $promoCodeValue): void;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void;

    /**
     * @return string
     */
    public function getStartDate(): string;

    /**
     * @param string $startDate
     * @return void
     */
    public function setStartDate(string $startDate): void;

    /**
     * @return string
     */
    public function getEndDate(): string;

    /**
     * @param string $endDate
     * @return void
     */
    public function setEndDate(string $endDate): void;

    /**
     * @return int
     */
    public function getPriority(): int;

    /**
     * @param int $priority
     * @return void
     */
    public function setPriority(int $priority): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailable\DetailsInterface
     */
    public function getDetails(): DetailsInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailable\DetailsInterface $details
     * @return void
     */
    public function setDetails(DetailsInterface $details): void;
}
