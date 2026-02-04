<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class SessionCookieConfigProvider
{
    private const XML_PATH_CART_URL = 'payment/inpost_pay/cart_url';
    private const XML_PATH_SEND_SESSION_COOKIE = 'payment/inpost_pay/send_session_cookie';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isSendingSessionCookieEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SEND_SESSION_COOKIE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCartUrl(?int $storeId = null): string
    {
        $url = $this->scopeConfig->getValue(self::XML_PATH_CART_URL, ScopeInterface::SCOPE_STORE, $storeId);
        $url = is_scalar($url) ? (string)$url : '';

        $baseUrl = $this->storeManager->getStore($storeId ?? 0)->getBaseUrl();

        return sprintf('%s/%s', trim($baseUrl, '/'), trim($url, '/'));
    }
}
