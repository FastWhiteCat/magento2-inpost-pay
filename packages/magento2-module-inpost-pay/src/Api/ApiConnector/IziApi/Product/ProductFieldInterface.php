<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\IziApi\Product;

interface ProductFieldInterface
{
    public const PRODUCTS = 'products';
    public const PRODUCT_ID = 'product_id';
    public const PRODUCT_CATEGORY = 'product_category';
    public const EAN = 'ean';
    public const PRODUCT_NAME = 'product_name';
    public const PRODUCT_DESCRIPTION = 'product_description';
    public const PRODUCT_LINK = 'product_link';
    public const PRODUCT_IMAGE = 'product_image';
    public const BASE_PRICE = 'base_price';
    public const QUANTITY = 'quantity';
    public const QUANTITY_TYPE = 'quantity_type';
    public const QUANTITY_UNIT = 'quantity_unit';
    public const AVAILABLE_QUANTITY = 'available_quantity';
    public const MAX_QUANTITY = 'max_quantity';
    public const PRODUCT_ATTRIBUTES = 'product_attributes';
    public const ATTRIBUTE_NAME = 'attribute_name';
    public const ATTRIBUTE_VALUE = 'attribute_value';
    public const VARIANTS = 'variants';
    public const RELATED_PRODUCTS = 'related_products';
}
