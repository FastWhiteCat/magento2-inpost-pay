<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Monolog\Logger;

class LogLevel implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'value' => Logger::DEBUG,
                'label' => __('DEBUG')
            ],
            [
                'value' => Logger::INFO,
                'label' => __('INFO')
            ],
            [
                'value' => Logger::NOTICE,
                'label' => __('NOTICE')
            ],
            [
                'value' => Logger::WARNING,
                'label' => __('WARNING')
            ],
            [
                'value' => Logger::ERROR,
                'label' => __('ERROR')
            ],
            [
                'value' => Logger::CRITICAL,
                'label' => __('CRITICAL')
            ],
            [
                'value' => Logger::ALERT,
                'label' => __('ALERT')
            ],
            [
                'value' => Logger::EMERGENCY,
                'label' => __('EMERGENCY')
            ]
        ];
    }
}
