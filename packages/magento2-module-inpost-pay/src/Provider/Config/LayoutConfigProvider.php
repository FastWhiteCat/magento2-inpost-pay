<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class LayoutConfigProvider
{
    private const XML_PATH_COLOR_VARIANT = 'payment/inpost_pay/widget_color_variant';
    private const XML_PATH_DARK_MODE = 'payment/inpost_pay/widget_dark_mode';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    /**
     * @return string
     */
    public function getColorVariant(): string
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_COLOR_VARIANT,
            ScopeInterface::SCOPE_STORE
        );

        return is_scalar($value) ? (string)$value : '';
    }

    /**
     * @return bool
     */
    public function isDarkModeEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DARK_MODE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
