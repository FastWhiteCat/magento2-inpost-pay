<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket\Product;

interface AdditionalProductImageInterface
{
    public const SMALL_SIZE = 'small_size';
    public const NORMAL_SIZE = 'normal_size';

    /**
     * @return string
     */
    public function getSmallSize(): string;

    /**
     * @param string $smallSize
     * @return void
     */
    public function setSmallSize(string $smallSize): void;

    /**
     * @return string
     */
    public function getNormalSize(): string;

    /**
     * @param string $normalSize
     * @return void
     */
    public function setNormalSize(string $normalSize): void;
}
