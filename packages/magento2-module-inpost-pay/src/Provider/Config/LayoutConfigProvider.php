<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class LayoutConfigProvider
{
    private const XML_PATH_SIZE = 'payment/inpost_pay/widget_size';
    private const XML_PATH_FRAME_STYLE = 'payment/inpost_pay/widget_frame_style';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    /**
     * @param int|null $websiteId
     * @return string
     */
    public function getWidgetStyles(?int $websiteId = null): string
    {
        $styles = array_merge($this->getFrameStyles($websiteId), [$this->getSize($websiteId)]);

        return implode(' ', $styles);
    }

    /**
     * @param int|null $websiteId
     * @return string
     */
    public function getSize(?int $websiteId = null): string
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_SIZE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        return is_scalar($value) ? (string)$value :'';
    }

    /**
     * @param int|null $websiteId
     * @return string[]
     */
    public function getFrameStyles(?int $websiteId = null): array
    {
        $values = $this->scopeConfig->getValue(
            self::XML_PATH_FRAME_STYLE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        return explode(',', is_scalar($values) ? (string)$values : '');
    }
}
