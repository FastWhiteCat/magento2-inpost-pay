<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use InPost\InPostPay\Provider\TestModeProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class SuccessPageUrlConfigProvider
{
    private const XML_PATH_SUCCESS_PAGE_URL = 'payment/inpost_pay/success_page_url';
    private const SUCCESS_PAGE_URL_ORDER_ID_VARIABLE = '{order_id}';
    private const SUCCESS_PAGE_URL_ORDER_INCREMENT_ID_VARIABLE = '{increment_id}';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param TestModeProvider $testModeProvider
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly TestModeProvider $testModeProvider
    ) {
    }

    /**
     * @param OrderInterface $order
     * @return string
     * @throws NoSuchEntityException
     */
    public function getOrderSuccessPageUrl(OrderInterface $order): string
    {
        $url = $this->scopeConfig->getValue(
            self::XML_PATH_SUCCESS_PAGE_URL,
            ScopeInterface::SCOPE_STORE,
            $order->getStoreId()
        );

        $orderId = is_scalar($order->getEntityId()) ? (string)$order->getEntityId() : '';
        $incrementId = is_scalar($order->getIncrementId()) ? (string)$order->getIncrementId() : '';

        $url = str_replace(
            self::SUCCESS_PAGE_URL_ORDER_ID_VARIABLE,
            $orderId,
            is_scalar($url) ? (string)$url : ''
        );
        $url = str_replace(self::SUCCESS_PAGE_URL_ORDER_INCREMENT_ID_VARIABLE, $incrementId, $url);

        if ($this->testModeProvider->isTestModeEnabled()) {
            if (str_contains($url, '?')) {
                $url = sprintf(
                    '%s&%s=%s',
                    trim($url, '/'),
                    TestModeProvider::URL_PARAMETER_NAME,
                    TestModeProvider::VALID_VALUE
                );
            } else {
                $url = sprintf(
                    '%s?%s=%s',
                    trim($url, '/'),
                    TestModeProvider::URL_PARAMETER_NAME,
                    TestModeProvider::VALID_VALUE
                );
            }
        }

        $baseUrl = $this->storeManager->getStore((int)$order->getStoreId())->getBaseUrl();

        return sprintf('%s/%s', trim($baseUrl, '/'), trim($url, '/'));
    }
}
