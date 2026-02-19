<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class SandboxConfigProvider
{
    public const SANDBOX_PREFIX = 'sandbox_';
    private const XML_PATH_SANDBOX_ENABLED = 'payment/inpost_pay/sandbox_enabled';

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
    public function isSandboxEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SANDBOX_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
