<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket\Product;

use InPost\InPostPay\Api\Data\Merchant\Basket\Product\AdditionalImageInterface;
use Magento\Framework\DataObject;

class AdditionalImage extends DataObject implements AdditionalImageInterface
{
    public function getSmallSize(): string
    {
        return $this->getData(self::SMALL_SIZE) ?? '';
    }

    public function setSmallSize(string $imageUrl): void
    {
        $this->setData(self::SMALL_SIZE, $imageUrl);
    }

    public function getNormalSize(): string
    {
        return $this->getData(self::NORMAL_SIZE) ?? '';
    }

    public function setNormalSize(string $imageUrl): void
    {
        $this->setData(self::NORMAL_SIZE, $imageUrl);
    }
}
