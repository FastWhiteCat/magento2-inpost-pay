<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use InPost\InPostPay\Exception\InPostPayInvalidConfigurationException;
use Magento\Framework\App\Config\ScopeConfigInterface;

class IziApiConfigProvider
{
    private const XML_PATH_IZI_API_URL = 'payment/inpost_pay/%sizi_api_url';
    private const XML_PATH_BASKET_LIFETIME = 'payment/inpost_pay/basket_lifetime';
    private const XML_PATH_ACCEPTED_PAYMENT_TYPES = 'payment/inpost_pay/accepted_payment_types';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param SandboxConfigProvider $sandboxConfigProvider
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly SandboxConfigProvider $sandboxConfigProvider
    ) {
    }

    /**
     * Returns production or sandbox Izi API URL
     *
     * @return string
     * @throws InPostPayInvalidConfigurationException
     */
    public function getIziApiUrl(): string
    {
        $iziApiUrl = $this->scopeConfig->getValue(
            sprintf(
                self::XML_PATH_IZI_API_URL,
                $this->sandboxConfigProvider->isSandboxEnabled() ? SandboxConfigProvider::SANDBOX_PREFIX : ''
            )
        );

        if (empty($iziApiUrl) || !is_scalar($iziApiUrl)) {
            throw new InPostPayInvalidConfigurationException(__('Empty IZI API URL'));
        }

        return (string)$iziApiUrl;
    }

    public function getBasketLifetime(): ?int
    {
        $basketLifetime = $this->scopeConfig->getValue(self::XML_PATH_BASKET_LIFETIME);

        if (!empty($basketLifetime) && is_scalar($basketLifetime)) {
            return (int)$basketLifetime;
        }

        return null;
    }

    public function getAcceptedPaymentTypes(): array
    {
        $acceptedPaymentTypes = $this->scopeConfig->getValue(self::XML_PATH_ACCEPTED_PAYMENT_TYPES);

        if (!empty($acceptedPaymentTypes) && is_scalar($acceptedPaymentTypes)) {
            return explode(',', (string)$acceptedPaymentTypes);
        }

        return [];
    }
}
