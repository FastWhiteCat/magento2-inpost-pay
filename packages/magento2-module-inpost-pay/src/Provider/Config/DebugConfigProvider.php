<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Monolog\Logger;

class DebugConfigProvider
{
    private const XML_PATH_LOG_LEVEL = 'payment/inpost_pay/min_log_level';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @return int
     */
    public function getMinLogLevel(): int
    {
        $minLogLevel = ($this->scopeConfig->getValue(self::XML_PATH_LOG_LEVEL) ?? Logger::DEBUG);

        return is_scalar($minLogLevel) ? (int)$minLogLevel : Logger::DEBUG;
    }
}
