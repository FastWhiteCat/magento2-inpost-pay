<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class BackgroundColor implements OptionSourceInterface
{
    public const LIGHT = 'light';
    public const DARK = 'dark';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::LIGHT,
                'label' => __('Light')
            ],
            [
                'value' => self::DARK,
                'label' => __('Dark')
            ]
        ];
    }
}
