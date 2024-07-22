<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class DisplayConfigProvider
{
    private const XML_PATH_WIDGET_ENABLED = 'payment/inpost_pay/widget_enabled';
    private const XML_PATH_ENABLED_ON_PRODUCT_CART = 'payment/inpost_pay/show_on_product_cart';
    private const XML_PATH_ENABLED_ON_CART = 'payment/inpost_pay/show_on_cart';
    private const XML_PATH_ENABLED_ON_CHECKOUT = 'payment/inpost_pay/show_on_checkout';
    private const XML_PATH_ENABLED_IN_MINICART = 'payment/inpost_pay/show_in_minicart';
    private const XML_PATH_ENABLED_ON_SUCCESS_PAGE = 'payment/inpost_pay/show_on_success_page';
    private const XML_PATH_ENABLED_ON_REGISTER_PAGE = 'payment/inpost_pay/show_on_register_page';
    private const XML_PATH_ENABLED_ON_LOGIN_PAGE = 'payment/inpost_pay/show_on_login_page';
    public const PRODUCT_CARD_BINDING_PLACE_NAME = 'PRODUCT_CARD';
    public const BASKET_SUMMARY_BINDING_PLACE_NAME = 'BASKET_SUMMARY';
    public const BASKET_POPUP_BINDING_PLACE_NAME = 'BASKET_POPUP';
    public const THANK_YOU_PAGE_BINDING_PLACE_NAME = 'ORDER_CREATE';
    public const REGISTER_PAGE_BINDING_PLACE_NAME = 'REGISTERFORM_PAGE';
    public const LOGIN_PAGE_BINDING_PLACE_NAME = 'LOGIN_PAGE';
    public const CHECKOUT_PAGE_BINDING_PLACE_NAME = 'CHECKOUT_PAGE';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    /**
     * @return bool
     */
    public function isWidgetEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_WIDGET_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnabledOnProductCart(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED_ON_PRODUCT_CART,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnabledOnCart(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED_ON_CART,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnabledInMiniCart(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED_IN_MINICART,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnabledOnSuccessPage(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED_ON_SUCCESS_PAGE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnabledOnRegisterPage(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED_ON_REGISTER_PAGE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnabledOnLoginPage(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED_ON_LOGIN_PAGE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnabledOnCheckoutPage(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED_ON_CHECKOUT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
