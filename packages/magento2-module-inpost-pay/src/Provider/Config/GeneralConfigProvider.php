<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class GeneralConfigProvider
{
    private const XML_PATH_INPOST_PAY_ENABLED = 'payment/inpost_pay/active';
    private const XML_PATH_INPOST_PAY_TEST_MODE_ENABLED = 'payment/inpost_pay/test_mode_active';
    private const XML_PATH_INPOST_PAY_NEW_ORDER_STATUS = 'payment/inpost_pay/order_status';
    private const XML_PATH_ORDER_ADDRESS_SOURCE_FLAG = 'payment/inpost_pay/use_address_as_firstname_source';
    private const XML_PATH_INPOST_PAY_IMAGE_ROLE = 'payment/inpost_pay/image_role';
    private const XML_PATH_INPOST_PAY_ADDITIONAL_IMAGES_ENABLED = 'payment/inpost_pay/additional_images_enabled';
    private const XML_PATH_INPOST_PAY_PREPARE_RESIZED_IMAGES = 'payment/inpost_pay/prepare_resized_additional_images';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    )
    {
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_INPOST_PAY_ENABLED,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return bool
     */
    public function isTestModeEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_INPOST_PAY_TEST_MODE_ENABLED,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return string
     */
    public function getNewOrderStatus(): string
    {
        $orderStatus = $this->scopeConfig->getValue(
            self::XML_PATH_INPOST_PAY_NEW_ORDER_STATUS,
            ScopeInterface::SCOPE_WEBSITE
        );

        return is_scalar($orderStatus) ? (string)$orderStatus : 'pending';
    }

    public function isUsingAddressAsDataSourceEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ORDER_ADDRESS_SOURCE_FLAG, ScopeInterface::SCOPE_WEBSITE);
    }

    public function getImageRole(): string
    {
        $orderStatus = $this->scopeConfig->getValue(
            self::XML_PATH_INPOST_PAY_IMAGE_ROLE,
            ScopeInterface::SCOPE_WEBSITE
        );

        return is_scalar($orderStatus) ? (string)$orderStatus : 'small_image';
    }

    public function isAdditionalImagesEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_INPOST_PAY_ADDITIONAL_IMAGES_ENABLED,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function isPrepareResizedImagesEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_INPOST_PAY_PREPARE_RESIZED_IMAGES,
            ScopeInterface::SCOPE_WEBSITE)
            ;
    }
}
