<?php

declare(strict_types=1);

namespace InPost\InPostPay\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Provider\Config\AuthConfigProvider;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Provider\TestModeProvider;
use InPost\InPostPay\Provider\Config\SandboxConfigProvider;
use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use InPost\InPostPay\Provider\Config\LayoutConfigProvider;
use InPost\InPostPay\Provider\Config\DisplayConfigProvider;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\Restrictions\Provider\RestrictedProductIdsProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Session\Config;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
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
     * @param ResolverInterface $localeResolver
     * @param CheckoutSession $checkoutSession
     * @param GeneralConfigProvider $generalConfigProvider
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param RestrictedProductIdsProvider $restrictedProductIdsProvider
     * @param LoggerInterface $logger
     * @param AuthConfigProvider $authConfigProvider
     * @param IziApiConfigProvider $iziApiConfigProvider
     * @param TestModeProvider $testModeProvider
     * @param ScopeConfigInterface $scopeConfig
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly SandboxConfigProvider $sandboxConfigProvider,
        private readonly LayoutConfigProvider $layoutConfigProvider,
        private readonly DisplayConfigProvider $displayConfigProvider,
        private readonly ResolverInterface $localeResolver,
        private readonly CheckoutSession $checkoutSession,
        private readonly GeneralConfigProvider $generalConfigProvider,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly RestrictedProductIdsProvider $restrictedProductIdsProvider,
        private readonly LoggerInterface $logger,
        private readonly AuthConfigProvider $authConfigProvider,
        private readonly IziApiConfigProvider $iziApiConfigProvider,
        private readonly TestModeProvider $testModeProvider,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->generalConfigProvider->isEnabled()
            && $this->displayConfigProvider->isWidgetEnabled()
            && $this->isDisplayAllowed();
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
        $product = $this->getProductById($productId);

        return $product && $product->isSaleable();
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
        $sandboxMode = $this->isSandboxEnabled();
        $scriptUrl = $sandboxMode
            ? "https://sandbox-inpostpay-widget-v2.inpost.pl/inpostpay.widget.v2.js"
            : "https://inpostpay-widget-v2.inpost.pl/inpostpay.widget.v2.js";

        if (!$this->isEnabledInMiniCart() || !$layout) {
            return $scriptUrl;
        }

        $isCheckoutPage = in_array(self::CHECKOUT_DESCRIPTOR, $layout);

        if ($isCheckoutPage && $bindingPlace === DisplayConfigProvider::BASKET_POPUP_BINDING_PLACE_NAME) {
            return '';
        }

        return $scriptUrl;
    }

    public function getApiBaseUrl(): string
    {
        return trim($this->iziApiConfigProvider->getIziApiUrl(), '/');
    }

    /**
     * @return string
     * @throws InPostPayInternalException
     */
    public function getClientMerchantId(): string
    {
        return $this->authConfigProvider->getClientMerchantId();
    }
}
