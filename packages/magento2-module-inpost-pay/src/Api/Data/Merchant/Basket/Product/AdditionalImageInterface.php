<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket\Product;

interface AdditionalImageInterface
{
    public const SMALL_SIZE = 'small_size';
    public const NORMAL_SIZE = 'normal_size';

    public const SMALL_SIZE_WIDTH = 1420;
    public const SMALL_SIZE_HEIGHT = 1390;
    public const NORMAL_SIZE_WIDTH = 1420;
    public const NORMAL_SIZE_HEIGHT = 2000;

    /**
     * @return string
     */
    public function getSmallSize(): string;

    /**
     * @param string $imageUrl
     * @return void
     */
    public function setSmallSize(string $imageUrl): void;

    /**
     * @return string
     */
    public function getNormalSize(): string;

    /**
     * @param string $imageUrl
     * @return void
     */
    public function setNormalSize(string $imageUrl): void;
}
