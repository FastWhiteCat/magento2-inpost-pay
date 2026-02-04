<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Sizes implements OptionSourceInterface
{
    public const SIZE_XS = 'size-xs';
    public const SIZE_SM = 'size-sm';
    public const SIZE_MD = 'size-md';
    public const SIZE_LG = 'size-lg';
    public const SIZE_XL = 'size-xl';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::SIZE_XS,
                'label' => __('Very Small')
            ],
            [
                'value' => self::SIZE_SM,
                'label' => __('Small')
            ],
            [
                'value' => self::SIZE_MD,
                'label' => __('Medium')
            ],
            [
                'value' => self::SIZE_LG,
                'label' => __('Large')
            ],
            [
                'value' => self::SIZE_XL,
                'label' => __('Very Large')
            ]
        ];
    }
}
