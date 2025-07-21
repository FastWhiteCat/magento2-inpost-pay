<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Order\OrderDetails;

use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetails\AdditionalOrderParametersInterface
    as AdditionalOrderParamsInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Api\ExtensibleDataInterface;

class AdditionalOrderParameters extends DataObject implements AdditionalOrderParamsInterface, ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getKey(): string
    {
        $key = $this->getData(self::KEY);

        return (is_scalar($key)) ? (string)$key : '';
    }

    /**
     * @param string $key
     * @return void
     */
    public function setKey(string $key): void
    {
        $this->setData(self::KEY, $key);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        $value = $this->getData(self::VALUE);

        return (is_scalar($value)) ? (string)$value : '';
    }

    /**
     * @param string $value
     * @return void
     */
    public function setValue(string $value): void
    {
        $this->setData(self::VALUE, $value);
    }
}
