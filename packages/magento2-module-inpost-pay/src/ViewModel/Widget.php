<?php

declare(strict_types=1);

namespace InPost\InPostPay\ViewModel;

use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Provider\Config\AnalyticsConfigProvider;
use InPost\InPostPay\Provider\Config\AuthConfigProvider;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Provider\TestModeProvider;
use InPost\InPostPay\Provider\Config\SandboxConfigProvider;
use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use InPost\InPostPay\Provider\Config\LayoutConfigProvider;
use InPost\InPostPay\Provider\Config\DisplayConfigProvider;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Validator\DigitalQuoteValidator;
use InPost\Restrictions\Provider\RestrictedProductIdsProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Widget implements ArgumentInterface
{
    private const CHECKOUT_DESCRIPTOR = 'checkout_index_index';

    /**
     * @param SandboxConfigProvider $sandboxConfigProvider
     * @param LayoutConfigProvider $layoutConfigProvider
     * @param DisplayConfigProvider $displayConfigProvider
     * @param AnalyticsConfigProvider $analyticsConfigProvider
     * @param ResolverInterface $localeResolver
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param GeneralConfigProvider $generalConfigProvider
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param RestrictedProductIdsProvider $restrictedProductIdsProvider
     * @param LoggerInterface $logger
     * @param AuthConfigProvider $authConfigProvider
     * @param IziApiConfigProvider $iziApiConfigProvider
     * @param DigitalQuoteValidator $digitalQuoteValidator
     * @param TestModeProvider $testModeProvider
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly SandboxConfigProvider $sandboxConfigProvider,
        private readonly LayoutConfigProvider $layoutConfigProvider,
        private readonly DisplayConfigProvider $displayConfigProvider,
        private readonly AnalyticsConfigProvider $analyticsConfigProvider,
        private readonly ResolverInterface $localeResolver,
        private readonly CheckoutSession $checkoutSession,
        private readonly CustomerSession $customerSession,
        private readonly GeneralConfigProvider $generalConfigProvider,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly RestrictedProductIdsProvider $restrictedProductIdsProvider,
        private readonly LoggerInterface $logger,
        private readonly AuthConfigProvider $authConfigProvider,
        private readonly IziApiConfigProvider $iziApiConfigProvider,
        private readonly DigitalQuoteValidator $digitalQuoteValidator,
        private readonly TestModeProvider $testModeProvider
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->generalConfigProvider->isEnabled()
            && $this->displayConfigProvider->isWidgetEnabled()
            && $this->isDisplayAllowed()
            && $this->isAuthConfigComplete();
    }

    public function isAnalyticsEnabled(): bool
    {
        return $this->analyticsConfigProvider->isAnalyticsEnabled();
    }

    /**
     * @return bool
     */
    private function isDisplayAllowed(): bool
    {
        if ($this->testModeProvider->isTestModeEnabled()) {
            return $this->testModeProvider->isTestModeRequested();
        }
        return true;
    }

    /**
     * @return string
     */
    public function getCurrentLanguageCode(): string
    {
        $currentCode = $this->localeResolver->getLocale();
        $currentCode = explode('_', $currentCode);

        return is_array($currentCode) && isset($currentCode[0]) ? $currentCode[0] : '';
    }

    /**
     * @return string
     */
    public function getLayoutConfig(): string
    {
        return $this->layoutConfigProvider->getWidgetStyles();
    }

    /**
     * @return bool
     */
    public function isSandboxEnabled(): bool
    {
        return $this->sandboxConfigProvider->isSandboxEnabled();
    }

    /**
     * @return bool
     */
    public function isEnabledOnProductCart(): bool
    {
        return $this->displayConfigProvider->isEnabledOnProductCart();
    }

    /**
     * @return bool
     */
    public function isEnabledOnCart(): bool
    {
        return $this->displayConfigProvider->isEnabledOnCart();
    }

    /**
     * @return bool
     */
    public function isEnabledInMiniCart(): bool
    {
        return $this->displayConfigProvider->isEnabledInMiniCart();
    }

    /**
     * @return bool
     */
    public function isEnabledOnSuccessPage(): bool
    {
        return $this->displayConfigProvider->isEnabledOnSuccessPage();
    }

    /**
     * @return bool
     */
    public function isEnabledOnRegisterPage(): bool
    {
        return $this->displayConfigProvider->isEnabledOnRegisterPage();
    }

    /**
     * @return bool
     */
    public function isEnabledOnLoginPage(): bool
    {
        return $this->displayConfigProvider->isEnabledOnLoginPage();
    }

    /**
     * @return bool
     */
    public function isEnabledOnCheckoutPage(): bool
    {
        return $this->displayConfigProvider->isEnabledOnCheckoutPage() && $this->isEnabled();
    }

    public function isProductRestricted(int $productId): bool
    {
        $websiteId = (int)$this->storeManager->getWebsite()->getId();

        return in_array(
            $productId,
            $this->restrictedProductIdsProvider->getList($websiteId)
        );
    }

    /**
     * Returns true if at least one product in cart is not restricted
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function canShowForWholeCart(): bool
    {
        foreach ($this->checkoutSession->getQuote()->getAllVisibleItems() as $item) {
            $productId = (int)$item->getProduct()->getId();
            if (!$this->isProductRestricted($productId)) {
                return true;
            }
        }

        return false;
    }

    public function isInPostPayOrder(): bool
    {
        try {
            $order = $this->checkoutSession->getLastRealOrder();
            $orderId = is_scalar($order->getId()) ? (int)$order->getId() : null;

            if (!$orderId) {
                return false;
            }
            $inpostOrder = $this->inPostPayOrderRepository->getByOrderId($orderId);
            if ($inpostOrder->getOrderId()) {
                return true;
            }
        } catch (LocalizedException) {
            return false;
        }

        return false;
    }

    public function validateProductIsSaleableById(int $productId): bool
    {
        $isValid = false;
        $product = $this->getProductById($productId);

        if ($product && $product->isSaleable()) {
            $isValid = true;
            $storeId = (int)$product->getStoreId();

            if ($product->isVirtual()
                && $this->digitalQuoteValidator->isLoggedInAccountRequiredForDigitalQuotes($storeId)
                && !$this->isCustomerLoggedIn()
            ) {
                $isValid = false;
            }
        }

        return $isValid;
    }

    private function getProductById(int $productId): ?Product
    {
        $product = null;
        $store = $this->storeManager->getStore();
        if ($store instanceof StoreInterface) {
            try {
                $product = $this->productRepository->getById($productId, false, $store->getId());
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return ($product instanceof Product) ? $product : null;
    }

    public function getScriptUrl(string $bindingPlace, array $layout = null): string
    {
        try {
            $scriptUrl = $this->iziApiConfigProvider->getWidgetUrl();
        } catch (InPostPayInternalException $e) {
            $this->logger->error($e->getMessage());

            return '';
        }

        if (!$this->isEnabledInMiniCart() || !$layout) {
            return $scriptUrl;
        }

        $isCheckoutPage = in_array(self::CHECKOUT_DESCRIPTOR, $layout);

        if ($isCheckoutPage && $bindingPlace === DisplayConfigProvider::BASKET_POPUP_BINDING_PLACE_NAME) {
            return '';
        }

        return $scriptUrl;
    }

    /**
     * @return string
     */
    public function getClientMerchantId(): string
    {
        try {
            return $this->authConfigProvider->getClientMerchantId();
        } catch (InPostPayInternalException $e) {
            $this->logger->error($e->getMessage());

            return '';
        }
    }

    /**
     * @return bool
     */
    private function isAuthConfigComplete(): bool
    {
        try {
            $merchantClientId = $this->authConfigProvider->getClientMerchantId();
            $posId = $this->authConfigProvider->getPosId();
            $clientId = $this->authConfigProvider->getClientId();
            $clientSecret = $this->authConfigProvider->getClientSecret();
            $authTokenUrl = $this->authConfigProvider->getAuthTokenUrl();
            $iziApiUrl = $this->iziApiConfigProvider->getIziApiUrl();
        } catch (InPostPayInternalException $e) {
            $this->logger->error($e->getMessage());
            $merchantClientId = '';
            $posId = '';
            $clientId = '';
            $clientSecret = '';
            $authTokenUrl = '';
            $iziApiUrl = '';
        }

        return !empty($posId)
            && !empty($clientId)
            && !empty($merchantClientId)
            && !empty($clientSecret)
            && !empty($authTokenUrl)
            && !empty($iziApiUrl);
    }

    /**
     * @return bool
     */
    private function isCustomerLoggedIn(): bool
    {
        try {
            $customerGroupId = (int)$this->customerSession->getCustomerGroupId();

            return $customerGroupId !== GroupManagement::NOT_LOGGED_IN_ID;
        } catch (NoSuchEntityException | LocalizedException $e) {
            return false;
        }
    }
}
