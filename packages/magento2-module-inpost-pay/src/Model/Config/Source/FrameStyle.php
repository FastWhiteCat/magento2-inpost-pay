<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FrameStyle implements OptionSourceInterface
{
    public const ROUNDED = 'rounded';
    public const ROUND = 'round';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::ROUNDED,
                'label' => __('small rounding')
            ],
            [
                'value' => self::ROUND,
                'label' => __('large rounding')
            ]
        ];
    }
}
