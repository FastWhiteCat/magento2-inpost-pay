<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Refund\EventData;

use InPost\InPostPay\Api\Data\Merchant\Refund\EventData\AmountInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class Amount extends DataObject implements AmountInterface, ExtensibleDataInterface
{
    private const DEFAULT_CURRENCY = 'PLN';

    /**
     * @return int
     */
    public function getValueInCents(): int
    {
        $valueInCents = $this->getData(self::VALUE_IN_CENTS);

        return (is_scalar($valueInCents)) ? (int)$valueInCents : 0;
    }

    /**
     * @param int $valueInCents
     * @return void
     */
    public function setValueInCents(int $valueInCents): void
    {
        $this->setData(self::VALUE_IN_CENTS, $valueInCents);
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        $currency = $this->getData(self::CURRENCY);

        return (is_scalar($currency)) ? (string)$currency : self::DEFAULT_CURRENCY;
    }

    /**
     * @param string $currency
     * @return void
     */
    public function setCurrency(string $currency): void
    {
        $this->setData(self::CURRENCY, $currency);
    }
}
