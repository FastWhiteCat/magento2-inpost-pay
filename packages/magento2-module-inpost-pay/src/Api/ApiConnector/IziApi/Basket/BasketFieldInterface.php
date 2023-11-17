<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\IziApi\Basket;

interface BasketFieldInterface
{
    public const BROWSER_ID = 'browser_id';
    public const SUMMARY = 'summary';
    public const BASKET_BASE_PRICE = 'basket_base_price';
    public const BASKET_FINAL_PRICE = 'basket_final_price';
    public const BASKET_PROMO_PRICE = 'basket_promo_price';
    public const NET = 'net';
    public const GROSS = 'gross';
    public const VAT = 'vat';
    public const CURRENCY = 'currency';
    public const BASKET_EXPIRATION_DATE = 'basket_expiration_date';
    public const BASKET_ADDITIONAL_INFORMATION = 'basket_additional_information';
    public const PAYMENT_TYPE = 'payment_type';
    public const BASKET_NOTICE = 'basket_notice';
    public const DELIVERY = 'delivery';
    public const DELIVERY_TYPE = 'delivery_type';

    public const DELIVERY_TYPE_COURIER = 'COURIER';
    public const DELIVERY_TYPE_PICKUP = 'APM';
    public const DELIVERY_OPTION_NAME = 'delivery_name';
    public const DELIVERY_OPTION_CODE = 'delivery_code_value';
    public const DELIVERY_OPTION_PRICE = 'delivery_option_price';
    public const DELIVERY_OPTION_CASH_ON_DELIVERY = 'COD';
    public const DELIVERY_DATE = 'delivery_date';
    public const DELIVERY_OPTIONS = 'delivery_options';
    public const DELIVERY_PRICE = 'delivery_price';
    public const FREE_DELIVERY_MINIMUM_GROSS_PRICE = 'free_delivery_minimum_gross_price';
    public const PROMO_CODES = 'promo_codes';
}
