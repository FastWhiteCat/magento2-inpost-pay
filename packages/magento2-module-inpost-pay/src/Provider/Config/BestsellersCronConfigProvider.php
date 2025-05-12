<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use InPost\InPostPay\Model\Config\Source\BestsellersSynchronizeMode;
use Magento\Framework\App\Config\ScopeConfigInterface;

class BestsellersCronConfigProvider
{
    private const XML_PATH_SYNCHRONIZATION_ENABLED = 'payment/inpost_pay/bestsellers_synchronize_enabled';
    private const XML_PATH_CRON_ENABLED = 'payment/inpost_pay/bestsellers_synchronize_cron_enabled';
    private const XML_PATH_SYNCHRO_MODE = 'payment/inpost_pay/bestsellers_synchronize_mode';

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
    public function isSynchronizationEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SYNCHRONIZATION_ENABLED);
    }

    /**
     * @return bool
     */
    public function isCronEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CRON_ENABLED);
    }

    /**
     * @return string
     */
    public function getSynchronizationMode(): string
    {
        $mode = $this->scopeConfig->getValue(self::XML_PATH_SYNCHRO_MODE);

        return is_scalar($mode) ? (string)$mode : BestsellersSynchronizeMode::UPLOAD;
    }
}
