<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Dto\Order;

class BasketPrice
{
    public const NET = 'net';
    public const GROSS = 'gross';
    public const VAT = 'vat';

    private ?float $net;
    private ?float $gross;
    private ?float $vat;

    public function getNet(): float
    {
        return (float)$this->net;
    }

    public function setNet(float $net): void
    {
        $this->net = $net;
    }

    public function getGross(): float
    {
        return (float)$this->gross;
    }

    public function setGross(float $gross): void
    {
        $this->gross = $gross;
    }

    public function getVat(): float
    {
        return (float)$this->vat;
    }

    public function setVat(float $vat): void
    {
        $this->vat = $vat;
    }
}
