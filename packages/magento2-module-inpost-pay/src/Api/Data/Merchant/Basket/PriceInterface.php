<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket;

interface PriceInterface
{
    public const NET = 'net';
    public const GROSS = 'gross';
    public const VAT = 'vat';

    /**
     * @return float
     */
    public function getNet(): float;

    /**
     * @param float $net
     * @return void
     */
    public function setNet(float $net): void;

    /**
     * @return float
     */
    public function getGross(): float;

    /**
     * @param float $gross
     * @return void
     */
    public function setGross(float $gross): void;

    /**
     * @return float
     */
    public function getVat(): float;

    /**
     * @param float $vat
     * @return void
     */
    public function setVat(float $vat): void;
}
