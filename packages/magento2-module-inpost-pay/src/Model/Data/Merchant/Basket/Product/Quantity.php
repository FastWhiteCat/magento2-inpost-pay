<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket\Product;

use InPost\InPostPay\Api\Data\Merchant\Basket\Product\QuantityInterface;
use InPost\InPostPay\Enum\InPostQuantityType;
use Magento\Framework\DataObject;

class Quantity extends DataObject implements QuantityInterface
{
    private const DEFAULT_UNIT = 'pcs';

    /**
     * @return float
     */
    public function getQuantity(): float
    {
        $quantity = $this->getData(self::QUANTITY);

        return is_scalar($quantity) ? (float)$quantity : 0.00;
    }

    /**
     * @param float $quantity
     * @return void
     */
    public function setQuantity(float $quantity): void
    {
        $this->setData(self::QUANTITY, $quantity);
    }

    /**
     * @return string
     */
    public function getQuantityType(): string
    {
        $quantityType = $this->getData(self::QUANTITY_TYPE);

        return is_scalar($quantityType) ? (string)$quantityType : InPostQuantityType::INTEGER->value;
    }

    /**
     * @param string $quantityType
     * @return void
     */
    public function setQuantityType(string $quantityType): void
    {
        if ($quantityType !== InPostQuantityType::INTEGER->value
            && $quantityType !== InPostQuantityType::DECIMAL->value
        ) {
            $quantityType = InPostQuantityType::INTEGER->value;
        }

        $this->setData(self::QUANTITY_TYPE, $quantityType);
    }

    /**
     * @return string
     */
    public function getQuantityUnit(): string
    {
        $quantityUnit = $this->getData(self::QUANTITY_UNIT);

        return is_scalar($quantityUnit) ? (string)$quantityUnit : self::DEFAULT_UNIT;
    }

    /**
     * @param string $quantityUnit
     * @return void
     */
    public function setQuantityUnit(string $quantityUnit): void
    {
        $this->setData(self::QUANTITY_UNIT, $quantityUnit);
    }

    /**
     * @return float
     */
    public function getAvailableQuantity(): float
    {
        $availableQuantity = $this->getData(self::AVAILABLE_QUANTITY);

        return is_scalar($availableQuantity) ? (float)$availableQuantity : 0.00;
    }

    /**
     * @param float $availableQuantity
     * @return void
     */
    public function setAvailableQuantity(float $availableQuantity): void
    {
        $this->setData(self::AVAILABLE_QUANTITY, $availableQuantity);
    }

    /**
     * @return float
     */
    public function getMaxQuantity(): float
    {
        $maxQuantity = $this->getData(self::MAX_QUANTITY);

        return is_scalar($maxQuantity) ? (float)$maxQuantity : 0.00;
    }

    /**
     * @param float $maxQuantity
     * @return void
     */
    public function setMaxQuantity(float $maxQuantity): void
    {
        $this->setData(self::MAX_QUANTITY, $maxQuantity);
    }
}
