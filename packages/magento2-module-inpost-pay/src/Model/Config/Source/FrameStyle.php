<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FrameStyle implements OptionSourceInterface
{
    public const ROUND = 'round';
    public const ROUNDED = 'rounded';
    public const DARK = 'dark';
    public const PRIMARY = 'primary';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::ROUND,
                'label' => __('%1 - max border radius', self::ROUND)
            ],
            [
                'value' => self::ROUNDED,
                'label' => __('%1 - border radius 8px, if together with \'round\' - \'round\' is used', self::ROUNDED)
            ],
            [
                'value' => self::DARK,
                'label' => __('%1 - footer text is white', self::DARK)
            ],
            [
                'value' => self::PRIMARY,
                'label' => __('%1 - widget background is yellow', self::PRIMARY)
            ],

        ];
    }
}
