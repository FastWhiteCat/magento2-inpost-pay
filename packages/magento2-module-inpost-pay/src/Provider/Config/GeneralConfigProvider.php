<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class GeneralConfigProvider
{
    private const XML_PATH_INPOST_PAY_ENABLED = 'payment/inpost_pay/enabled';
    private const XML_PATH_INPOST_PAY_NEW_ORDER_STATUS = 'payment/inpost_pay/order_status';

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
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_INPOST_PAY_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getNewOrderStatus(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_INPOST_PAY_NEW_ORDER_STATUS,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
