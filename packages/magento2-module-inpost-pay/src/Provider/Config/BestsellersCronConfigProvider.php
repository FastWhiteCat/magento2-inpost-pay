<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class BestsellersCronConfigProvider
{
    private const XML_PATH_SYNCHRONIZATION_ENABLED = 'payment/inpost_pay/synchronize_bestsellers_enabled';
    private const XML_PATH_CRON_ENABLED = 'payment/inpost_pay/bestsellers_synchronize_cron_enabled';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isSynchronizationEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SYNCHRONIZATION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return bool
     */
    public function isCronEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CRON_ENABLED);
    }
}
