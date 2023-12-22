<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class Price extends DataObject implements PriceInterface, ExtensibleDataInterface
{
    /**
     * @return float
     */
    public function getNet(): float
    {
        $net = $this->getData(self::NET);

        return is_scalar($net) ? (float)$net : 0.00;
    }

    /**
     * @param float $net
     * @return void
     */
    public function setNet(float $net): void
    {
        $this->setData(self::NET, $net);
    }

    /**
     * @return float
     */
    public function getGross(): float
    {
        $gross = $this->getData(self::GROSS);

        return is_scalar($gross) ? (float)$gross : 0.00;
    }

    /**
     * @param float $gross
     * @return void
     */
    public function setGross(float $gross): void
    {
        $this->setData(self::GROSS, $gross);
    }

    /**
     * @return float
     */
    public function getVat(): float
    {
        $vat = $this->getData(self::VAT);

        return is_scalar($vat) ? (float)$vat : 0.00;
    }

    /**
     * @param float $vat
     * @return void
     */
    public function setVat(float $vat): void
    {
        $this->setData(self::VAT, $vat);
    }
}
