<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FrameStyle implements OptionSourceInterface
{
    public const SQUARED = 'squared';
    public const ROUND = 'round';
    public const ROUNDED = 'rounded';
    public const DARK = 'dark';
    public const PRIMARY = 'primary';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::SQUARED,
                'label' => __('Squared')
            ],
            [
                'value' => self::ROUNDED,
                'label' => __('Rounded')
            ],
            [
                'value' => self::ROUND,
                'label' => __('Round')
            ]
        ];
    }
}
