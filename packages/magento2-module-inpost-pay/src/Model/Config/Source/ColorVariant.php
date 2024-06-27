<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ColorVariant implements OptionSourceInterface
{
    public const PRIMARY = 'primary';
    public const SECONDARY = 'secondary';
    public const THIRD = 'third';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::PRIMARY,
                'label' => __('primary')
            ],
            [
                'value' => self::SECONDARY,
                'label' => __('secondary')
            ],
            [
                'value' => self::THIRD,
                'label' => __('third')
            ]
        ];
    }
}
