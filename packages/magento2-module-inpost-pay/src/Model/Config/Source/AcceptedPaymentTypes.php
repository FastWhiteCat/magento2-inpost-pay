<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Monolog\Logger;

class AcceptedPaymentTypes implements OptionSourceInterface
{
    public const CARD = 'CARD';
    public const CARD_TOKEN = 'CARD_TOKEN';
    public const GOOGLE_PAY = 'GOOGLE_PAY';
    public const APPLE_PAY = 'APPLE_PAY';
    public const BLIK_CODE = 'BLIK_CODE';
    public const BLIK_TOKEN = 'BLIK_TOKEN';
    public const PAY_BY_LINK = 'PAY_BY_LINK';
    public const SHOPPING_LIMIT = 'SHOPPING_LIMIT';
    public const DEFERRED_PAYMENT = 'DEFERRED_PAYMENT';
    public const CASH_ON_DELIVERY = 'CASH_ON_DELIVERY';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::CARD,
                'label' => __('Card')
            ],
            [
                'value' => self::CARD_TOKEN,
                'label' => __('Card token')
            ],
            [
                'value' => self::GOOGLE_PAY,
                'label' => __('Google Pay')
            ],
            [
                'value' => self::APPLE_PAY,
                'label' => __('Apple Pay')
            ],
            [
                'value' => self::BLIK_CODE,
                'label' => __('BLIK code')
            ],
            [
                'value' => self::BLIK_TOKEN,
                'label' => __('BLIK token')
            ],
            [
                'value' => self::PAY_BY_LINK,
                'label' => __('Pay by link')
            ],
            [
                'value' => self::SHOPPING_LIMIT,
                'label' => __('Shopping limit')
            ],
            [
                'value' => self::DEFERRED_PAYMENT,
                'label' => __('Deferred payment')
            ],
            [
                'value' => self::CASH_ON_DELIVERY,
                'label' => __('Cash on delivery')
            ]
        ];
    }
}
