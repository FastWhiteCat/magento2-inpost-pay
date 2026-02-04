<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use InPost\InPostPay\Model\Config\Source\BackgroundColor;
use InPost\InPostPay\Model\Config\Source\ColorVariant;
use InPost\InPostPay\Model\Config\Source\FrameStyle;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class LayoutConfigProvider
{
    private const XML_PATH_BACKGROUND_COLOR = 'payment/inpost_pay/widget_background_color';
    private const XML_PATH_COLOR_VARIANT = 'payment/inpost_pay/widget_color_variant';
    private const XML_PATH_SIZE = 'payment/inpost_pay/widget_size';
    private const XML_PATH_FRAME_SHAPE = 'payment/inpost_pay/widget_frame_shape';

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
        $styles = [
            $this->getBackgroundColor($websiteId),
            $this->getColorVariant($websiteId),
            $this->getFrameShape($websiteId),
            $this->getSize($websiteId)
        ];

        return (string)preg_replace('/\s+/', ' ', trim(implode(' ', $styles)));
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
     * @return string
     */
    public function getFrameShape(?int $websiteId = null): string
    {
        $frameShape = $this->scopeConfig->getValue(
            self::XML_PATH_FRAME_SHAPE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        $frameShape = is_scalar($frameShape) ? (string)$frameShape : FrameStyle::SQUARED;

        return empty($frameShape) ? FrameStyle::SQUARED : $frameShape;
    }

    /**
     * @param int|null $websiteId
     * @return string
     */
    public function getBackgroundColor(?int $websiteId = null): string
    {
        $backgroundColor = $this->scopeConfig->getValue(
            self::XML_PATH_BACKGROUND_COLOR,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        $backgroundColor = is_scalar($backgroundColor) ? (string)$backgroundColor : BackgroundColor::LIGHT;

        return $backgroundColor === BackgroundColor::LIGHT ? '' : BackgroundColor::DARK;
    }

    /**
     * @param int|null $websiteId
     * @return string
     */
    public function getColorVariant(?int $websiteId = null): string
    {
        $colorVariant = $this->scopeConfig->getValue(
            self::XML_PATH_COLOR_VARIANT,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        $colorVariant = is_scalar($colorVariant) ? (string)$colorVariant : ColorVariant::PRIMARY;

        return $colorVariant === ColorVariant::SECONDARY ? '' : ColorVariant::PRIMARY;
    }
}
