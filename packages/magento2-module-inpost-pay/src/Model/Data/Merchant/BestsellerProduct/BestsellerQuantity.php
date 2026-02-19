<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\BestsellerProduct;

use InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\BestsellerQuantityInterface;
use InPost\InPostPay\Enum\InPostQuantityType;
use Magento\Framework\DataObject;
use Magento\Framework\Api\ExtensibleDataInterface;

class BestsellerQuantity extends DataObject implements BestsellerQuantityInterface, ExtensibleDataInterface
{
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
     * @return string|null
     */
    public function getQuantityUnit(): ?string
    {
        $quantityUnit = $this->getData(self::QUANTITY_UNIT);

        return is_scalar($quantityUnit) ? (string)$quantityUnit : null;
    }

    /**
     * @param string|null $quantityUnit
     * @return void
     */
    public function setQuantityUnit(?string $quantityUnit): void
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
}
