<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ColorVariant implements OptionSourceInterface
{
    public const PRIMARY = 'primary';
    public const SECONDARY = 'black';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::PRIMARY,
                'label' => __('Yellow')
            ],
            [
                'value' => self::SECONDARY,
                'label' => __('Black')
            ]
        ];
    }
}
