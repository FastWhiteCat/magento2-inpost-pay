<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class BestsellersSynchronizeMode implements OptionSourceInterface
{
    public const DOWNLOAD = 'download';
    public const UPLOAD = 'upload';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::DOWNLOAD,
                'label' => __('Download and replace Magento Bestsellers with InPost Pay Configured Bestsellers.')
            ],
            [
                'value' => self::UPLOAD,
                'label' => __('Upload Magento Bestsellers into InPost Pay API.')
            ]
        ];
    }
}
