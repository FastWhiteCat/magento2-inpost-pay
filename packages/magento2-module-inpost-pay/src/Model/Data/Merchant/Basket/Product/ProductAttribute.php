<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket\Product;

use InPost\InPostPay\Api\Data\Merchant\Basket\Product\ProductAttributeInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class ProductAttribute extends DataObject implements ProductAttributeInterface, ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getAttributeName(): string
    {
        $attributeName = $this->getData(self::ATTRIBUTE_NAME);

        return is_scalar($attributeName) ? (string)$attributeName : '';
    }

    /**
     * @param string $attributeName
     * @return void
     */
    public function setAttributeName(string $attributeName): void
    {
        $this->setData(self::ATTRIBUTE_NAME, $attributeName);
    }

    /**
     * @return string
     */
    public function getAttributeValue(): string
    {
        $attributeValue = $this->getData(self::ATTRIBUTE_VALUE);

        return is_scalar($attributeValue) ? (string)$attributeValue : '';
    }

    /**
     * @param string $attributeValue
     * @return void
     */
    public function setAttributeValue(string $attributeValue): void
    {
        $this->setData(self::ATTRIBUTE_VALUE, $attributeValue);
    }
}
