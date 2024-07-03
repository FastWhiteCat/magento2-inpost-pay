<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class PoolingConfigProvider
{
    private const XML_PATH_LONG_POOLING_TIME_FOR_INACTIVE_TAB = 'payment/inpost_pay/pooling_time_for_inactive';
    private const XML_PATH_ENABLED_LONG_POOLING_FOR_INACTIVE_TAB = 'payment/inpost_pay/pooling_enabled_for_inactive';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    )
    {
    }

    /**
     * @return int
     */
    public function getLongPollingTimeForInactiveTab(): int
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_LONG_POOLING_TIME_FOR_INACTIVE_TAB,
            ScopeInterface::SCOPE_WEBSITE
        );

        return is_scalar($value) ? (int)$value : 0;
    }

    /**
     * @return bool
     */
    public function isEnabledLongPollingForInactiveTab(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED_LONG_POOLING_FOR_INACTIVE_TAB,
            ScopeInterface::SCOPE_STORE
        );
    }
}
