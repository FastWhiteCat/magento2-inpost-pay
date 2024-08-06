<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Basket\Product;

interface ProductAttributeInterface
{
    public const ATTRIBUTE_NAME = 'attribute_name';
    public const ATTRIBUTE_VALUE = 'attribute_value';

    /**
     * @return string
     */
    public function getAttributeName(): string;

    /**
     * @param string $attributeName
     * @return void
     */
    public function setAttributeName(string $attributeName): void;

    /**
     * @return string
     */
    public function getAttributeValue(): string;

    /**
     * @param string $attributeValue
     * @return void
     */
    public function setAttributeValue(string $attributeValue): void;
}
