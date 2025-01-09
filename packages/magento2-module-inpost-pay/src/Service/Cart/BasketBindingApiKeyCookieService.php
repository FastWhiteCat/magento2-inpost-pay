<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Cart;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Session\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class BasketBindingApiKeyCookieService
{
    public const BASKET_BINDING_API_KEY_COOKIE = 'basketBindingApiKey';

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CookieManagerInterface $cookieManager,
        private readonly CookieMetadataFactory $cookieMetadataFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly LoggerInterface $logger
    ) {
    }

    public function createOrUpdateBasketBindingCookie(string $basketBindingApiKey): void
    {
        try {
            $value = $this->cookieManager->getCookie(self::BASKET_BINDING_API_KEY_COOKIE);

            if ($value === $basketBindingApiKey) {
                return;
            }

            if ($value) {
                $this->cookieManager->deleteCookie(self::BASKET_BINDING_API_KEY_COOKIE);
            }

            if ($basketBindingApiKey) {
                // @phpstan-ignore-next-line
                $storePath = $this->storeManager->getStore()->getStorePath();

                // @phpstan-ignore-next-line
                $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                    ->setHttpOnly(false)
                    ->setDuration((int)$this->getCookieLifeTime())
                    ->setPath($storePath)
                    ->setSameSite('Lax');

                $this->cookieManager->setPublicCookie(
                    self::BASKET_BINDING_API_KEY_COOKIE,
                    $basketBindingApiKey,
                    $metadata
                );
            }
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Unable to update Basket Binding API Key cookie. Reason: %s', $e->getMessage())
            );
        }
    }

    public function deleteBasketBindingKeyCookie(): void
    {
        try {
            $value = $this->cookieManager->getCookie(self::BASKET_BINDING_API_KEY_COOKIE);

            if ($value) {
                // @phpstan-ignore-next-line
                $storePath = $this->storeManager->getStore()->getStorePath();
                // @phpstan-ignore-next-line
                $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                    ->setHttpOnly(false)
                    ->setDuration((int)$this->getCookieLifeTime())
                    ->setPath($storePath)
                    ->setSameSite('Lax');

                $this->cookieManager->deleteCookie(self::BASKET_BINDING_API_KEY_COOKIE, $metadata);
            }
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Unable to delete Basket Binding API Key cookie. Reason: %s', $e->getMessage())
            );
        }
    }

    /**
     * Get CookieLifeTime
     * @return int scopeCode
     */
    public function getCookieLifeTime(): int
    {
        $cookieLifetime = $this->scopeConfig->getValue(
            Config::XML_PATH_COOKIE_LIFETIME,
            ScopeInterface::SCOPE_STORE
        );

        return is_scalar($cookieLifetime) ? (int)$cookieLifetime : Config::COOKIE_LIFETIME_DEFAULT;
    }
}
