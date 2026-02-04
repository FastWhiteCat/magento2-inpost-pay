<?php

declare(strict_types=1);

namespace InPost\Restrictions\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class RestrictionsConfigProvider
{
    private const XML_PATH_CRON_RESTRICTIONS_REFRESH_ENABLED = 'payment/inpost_pay/enable_restrictions_refresh_cron';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CRON_RESTRICTIONS_REFRESH_ENABLED);
    }
}
