<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket\Product;

use InPost\InPostPay\Api\Data\Merchant\Basket\Product\AdditionalProductImageInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class AdditionalProductImage extends DataObject implements AdditionalProductImageInterface, ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getSmallSize(): string
    {
        $smallSize = $this->getData(self::SMALL_SIZE);

        return is_scalar($smallSize) ? (string)$smallSize : '';
    }

    /**
     * @param string $smallSize
     * @return void
     */
    public function setSmallSize(string $smallSize): void
    {
        $this->setData(self::SMALL_SIZE, $smallSize);
    }

    /**
     * @return string
     */
    public function getNormalSize(): string
    {
        $normalSize = $this->getData(self::NORMAL_SIZE);

        return is_scalar($normalSize) ? (string)$normalSize : '';
    }

    /**
     * @param string $normalSize
     * @return void
     */
    public function setNormalSize(string $normalSize): void
    {
        $this->setData(self::NORMAL_SIZE, $normalSize);
    }
}
