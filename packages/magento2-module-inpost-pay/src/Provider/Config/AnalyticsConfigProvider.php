<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class AnalyticsConfigProvider
{
    private const XML_PATH_ANALYTICS_ENABLED = 'payment/inpost_pay/analytics_enabled';
    private const XML_PATH_GA_MEASUREMENT_ID = 'payment/inpost_pay/ga_measurement_id';
    private const XML_PATH_GA_API_SECRET = 'payment/inpost_pay/ga_api_secret';
    private const XML_PATH_GA_API_URL = 'payment/inpost_pay/ga_api_url';
    private const XML_PATH_FBCLID_SENDING_ENABLED = 'payment/inpost_pay/sending_fbclid_enabled';
    private const XML_PATH_GCLID_SENDING_ENABLED = 'payment/inpost_pay/sending_gclid_enabled';
    private const XML_PATH_ASYNC_SENDING_ENABLED = 'payment/inpost_pay/analytics_async_sending_enabled';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isAnalyticsEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ANALYTICS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return bool
     */
    public function isAsyncSendingEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ASYNC_SENDING_ENABLED);
    }

    /**
     * @param int|null $storeId
     * @return string|null
     */
    public function getGaMeasurementId(?int $storeId = null): ?string
    {
        $gaMeasurementId = $this->scopeConfig->getValue(
            self::XML_PATH_GA_MEASUREMENT_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return is_scalar($gaMeasurementId) && !empty($gaMeasurementId) ? (string)$gaMeasurementId : null;
    }

    /**
     * @param int|null $storeId
     * @return string|null
     */
    public function getGaApiSecret(?int $storeId = null): ?string
    {
        $gaApiSecret = $this->scopeConfig->getValue(
            self::XML_PATH_GA_API_SECRET,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return is_scalar($gaApiSecret) && !empty($gaApiSecret) ? (string)$gaApiSecret : null;
    }

    /**
     * @return string|null
     */
    public function getGaApiUrl(): ?string
    {
        $gaApiUrl = $this->scopeConfig->getValue(self::XML_PATH_GA_API_URL);

        return is_scalar($gaApiUrl) && !empty($gaApiUrl) ? (string)$gaApiUrl : null;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isSendingFbclidEnabled(?int $storeId = null): bool
    {
        $isSendingFbclidEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_FBCLID_SENDING_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $this->isAnalyticsEnabled($storeId) && $isSendingFbclidEnabled;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isSendingGclidEnabled(?int $storeId = null): bool
    {
        $isSendingGclidEnabled = $this->scopeConfig->isSetFlag(
            self::XML_PATH_GCLID_SENDING_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $this->isAnalyticsEnabled($storeId) && $isSendingGclidEnabled;
    }
}
