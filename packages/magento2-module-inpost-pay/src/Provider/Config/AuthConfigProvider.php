<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use InPost\InPostPay\Exception\InPostPayInternalException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class AuthConfigProvider
{
    private const XML_PATH_CLIENT_ID = 'payment/inpost_pay/%sclient_id';
    private const XML_PATH_CLIENT_SECRET = 'payment/inpost_pay/%sclient_secret';
    private const XML_PATH_MERCHANT_SECRET = 'payment/inpost_pay/%smerchant_secret';
    private const XML_PATH_AUTH_TOKEN_URL = 'payment/inpost_pay/%sauth_token_url';
    private const XML_PATH_POS_ID = 'payment/inpost_pay/%spos_id';
    private const XML_PATH_CLIENT_MERCHANT_ID = 'payment/inpost_pay/%sclient_merchant_id';

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
     * Returns production or sandbox Client ID
     *
     * @param int|null $storeId
     * @return string
     * @throws InPostPayInternalException
     */
    public function getClientId(?int $storeId = null): string
    {
        $clientId = $this->scopeConfig->getValue(
            sprintf(
                self::XML_PATH_CLIENT_ID,
                $this->sandboxConfigProvider->isSandboxEnabled($storeId) ? SandboxConfigProvider::SANDBOX_PREFIX : ''
            ),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($clientId) || !is_scalar($clientId)) {
            throw new InPostPayInternalException(__('Empty Client ID'));
        }

        return (string)$clientId;
    }

    /**
     * Returns production or sandbox Client Secret
     *
     * @param int|null $storeId
     * @return string
     * @throws InPostPayInternalException
     */
    public function getClientSecret(?int $storeId = null): string
    {
        $clientSecret = $this->scopeConfig->getValue(
            sprintf(
                self::XML_PATH_CLIENT_SECRET,
                $this->sandboxConfigProvider->isSandboxEnabled($storeId) ? SandboxConfigProvider::SANDBOX_PREFIX : ''
            ),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($clientSecret) || !is_scalar($clientSecret)) {
            throw new InPostPayInternalException(__('Empty Client Secret'));
        }

        return (string)$clientSecret;
    }

    public function getMerchantSecret(?int $storeId = null): string
    {
        $merchantSecret = $this->scopeConfig->getValue(
            sprintf(
                self::XML_PATH_MERCHANT_SECRET,
                $this->sandboxConfigProvider->isSandboxEnabled($storeId) ? SandboxConfigProvider::SANDBOX_PREFIX : ''
            ),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($merchantSecret) || !is_scalar($merchantSecret)) {
            throw new InPostPayInternalException(__('Empty Merchant Secret'));
        }

        return (string)$merchantSecret;
    }

    /**
     * Returns production or sandbox Token providing API URL
     *
     * @param int|null $storeId
     * @return string
     * @throws InPostPayInternalException
     */
    public function getAuthTokenUrl(?int $storeId = null): string
    {
        $authTokenUrl = $this->scopeConfig->getValue(
            sprintf(
                self::XML_PATH_AUTH_TOKEN_URL,
                $this->sandboxConfigProvider->isSandboxEnabled($storeId) ? SandboxConfigProvider::SANDBOX_PREFIX : ''
            ),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($authTokenUrl) || !is_scalar($authTokenUrl)) {
            throw new InPostPayInternalException(__('Empty Auth Token URL'));
        }

        return (string)$authTokenUrl;
    }

    /**
     * Returns production or sandbox POS ID
     *
     * @param int|null $storeId
     * @return string
     * @throws InPostPayInternalException
     */
    public function getPosId(?int $storeId = null): string
    {
        $posId = $this->scopeConfig->getValue(
            sprintf(
                self::XML_PATH_POS_ID,
                $this->sandboxConfigProvider->isSandboxEnabled($storeId) ? SandboxConfigProvider::SANDBOX_PREFIX : ''
            ),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($posId) || !is_scalar($posId)) {
            throw new InPostPayInternalException(__('Empty POS ID'));
        }

        return (string)$posId;
    }

    /**
     * Returns Client Merchant Id
     *
     * @param int|null $storeId
     * @return string
     * @throws InPostPayInternalException
     */
    public function getClientMerchantId(?int $storeId = null): string
    {
        $clientMerchantId = $this->scopeConfig->getValue(
            sprintf(
                self::XML_PATH_CLIENT_MERCHANT_ID,
                $this->sandboxConfigProvider->isSandboxEnabled($storeId) ? SandboxConfigProvider::SANDBOX_PREFIX : ''
            ),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($clientMerchantId) || !is_scalar($clientMerchantId)) {
            throw new InPostPayInternalException(__('Empty Client Merchant Id'));
        }

        return (string)$clientMerchantId ;
    }
}
