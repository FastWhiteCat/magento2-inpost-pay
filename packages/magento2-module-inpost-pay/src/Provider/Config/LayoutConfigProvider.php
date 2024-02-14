<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class LayoutConfigProvider
{
    private const XML_PATH_COLOR_VARIANT = 'payment/inpost_pay/widget_color_variant';
    private const XML_PATH_DARK_MODE = 'payment/inpost_pay/widget_dark_mode';

    private const XML_PATH_MAX_WIDTH = 'payment/inpost_pay/widget_max_width';

    private const XML_PATH_FRAME_STYLE = 'payment/inpost_pay/widget_frame_style';

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
            ScopeInterface::SCOPE_WEBSITE
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
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return int
     */
    public function getMaxWidth(): int
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_MAX_WIDTH,
            ScopeInterface::SCOPE_WEBSITE
        );

        return is_scalar($value) ? (int)$value : 0;
    }

    /**
     * @param int|null $websiteId
     * @return string
     */
    public function getFrameStyle(?int $websiteId = null): string
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_FRAME_STYLE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        return is_scalar($value) ? (string)$value : '';
    }
}
