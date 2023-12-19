<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket\Product;

use InPost\InPostPay\Api\Data\Merchant\Basket\Product\QuantityChangeInterface;
use Magento\Framework\DataObject;

class QuantityChange extends DataObject implements QuantityChangeInterface
{
    /**
     * @return float|int
     */
    public function getQuantity(): float|int
    {
        $quantity = $this->getData(self::QUANTITY);

        if (is_float($quantity) || is_int($quantity)) {
            return $quantity;
        }

        return (is_scalar($quantity)) ? (int)$quantity : 1;
    }

    /**
     * @param float|int $quantity
     * @return void
     */
    public function setQuantity(float|int $quantity): void
    {
        $this->setData(self::QUANTITY, $quantity);
    }
}
