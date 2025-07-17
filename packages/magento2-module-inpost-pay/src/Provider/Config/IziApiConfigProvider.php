<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use InPost\InPostPay\Api\InPostPayAvailablePaymentMethodRepositoryInterface;
use InPost\InPostPay\Exception\InPostPayInternalException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class IziApiConfigProvider
{
    private const XML_PATH_IZI_API_URL = 'payment/inpost_pay/%sizi_api_url';
    private const XML_PATH_WIDGET_URL = 'payment/inpost_pay/%swidget_script_url';
    private const XML_PATH_BASKET_LIFETIME = 'payment/inpost_pay/basket_lifetime';
    private const XML_PATH_ACCEPTED_PAYMENT_TYPES = 'payment/inpost_pay/accepted_payment_types';
    private const XML_PATH_USE_DEFINED_PAYMENT_METHODS = 'payment/inpost_pay/use_defined_payment_methods';
    private const XML_PATH_ASYNC_BASKET_EXPORT = 'payment/inpost_pay/async_basket_export';
    private const XML_PATH_PROD_ATTR_CLEANING = 'payment/inpost_pay/remove_html_and_special_chars_from_attributes';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param SandboxConfigProvider $sandboxConfigProvider
     * @param InPostPayAvailablePaymentMethodRepositoryInterface $availablePaymentMethodRepository
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly SandboxConfigProvider $sandboxConfigProvider,
        private readonly InPostPayAvailablePaymentMethodRepositoryInterface $availablePaymentMethodRepository
    ) {
    }

    /**
     * Returns production or sandbox Izi API URL
     *
     * @param int|null $storeId
     * @return string
     * @throws InPostPayInternalException
     */
    public function getIziApiUrl(?int $storeId = null): string
    {
        $iziApiUrl = $this->scopeConfig->getValue(
            sprintf(
                self::XML_PATH_IZI_API_URL,
                $this->sandboxConfigProvider->isSandboxEnabled() ? SandboxConfigProvider::SANDBOX_PREFIX : ''
            ),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($iziApiUrl) || !is_scalar($iziApiUrl)) {
            throw new InPostPayInternalException(__('Empty IZI API URL'));
        }

        return (string)$iziApiUrl;
    }

    /**
     * Returns production or sandbox Widget URL
     *
     * @return string
     * @throws InPostPayInternalException
     */
    public function getWidgetUrl(): string
    {
        $widgetUrl = $this->scopeConfig->getValue(
            sprintf(
                self::XML_PATH_WIDGET_URL,
                $this->sandboxConfigProvider->isSandboxEnabled() ? SandboxConfigProvider::SANDBOX_PREFIX : ''
            )
        );

        if (empty($widgetUrl) || !is_scalar($widgetUrl)) {
            throw new InPostPayInternalException(__('Empty Widget URL'));
        }

        return (string)$widgetUrl;
    }

    public function getBasketLifetime(): ?int
    {
        $basketLifetime = $this->scopeConfig->getValue(self::XML_PATH_BASKET_LIFETIME);

        if (!empty($basketLifetime) && is_scalar($basketLifetime)) {
            return (int)$basketLifetime;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isUsingDefinedMethodsEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_USE_DEFINED_PAYMENT_METHODS);
    }

    public function getAcceptedPaymentTypes(): array
    {
        if (!$this->isUsingDefinedMethodsEnabled()) {
            return [];
        }

        $acceptedPaymentTypes = $this->scopeConfig->getValue(self::XML_PATH_ACCEPTED_PAYMENT_TYPES);

        if (!empty($acceptedPaymentTypes) && is_scalar($acceptedPaymentTypes)) {
            $acceptedPaymentTypes = explode(',', (string)$acceptedPaymentTypes);
            $availablePaymentMethodsCodes = $this->getAvailablePaymentMethodsCodes();

            return array_intersect(
                $acceptedPaymentTypes,
                $availablePaymentMethodsCodes
            );
        }

        return [];
    }

    public function isAsyncBasketExportEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ASYNC_BASKET_EXPORT);
    }

    public function isProductAttributesHTMLAndSpecialCharactersCleaningEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_PROD_ATTR_CLEANING);
    }

    private function getAvailablePaymentMethodsCodes(): array
    {
        $availablePaymentTypes = $this->availablePaymentMethodRepository->getAllValuesAsArray();

        return !empty($availablePaymentTypes) ? array_column($availablePaymentTypes, 'payment_code') : [];
    }
}
