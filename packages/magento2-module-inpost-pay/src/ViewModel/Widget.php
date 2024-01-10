<?php

declare(strict_types=1);

namespace InPost\InPostPay\ViewModel;

use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use InPost\InPostPay\Provider\Config\LayoutConfigProvider;
use InPost\InPostPay\Provider\Config\DisplayConfigProvider;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Widget implements ArgumentInterface
{
    private const VARIANT = 'variant';
    private const DARK_MODE = 'darkMode';
    private const MAX_WIDTH = 'maxWidth';
    private const FRAME_STYLE = 'frameStyle';

    private const NOT_ALLOWED_PRODUCT_TYPES = ['bundle', 'grouped'];

    /**
     * @param LayoutConfigProvider $layoutConfigProvider
     * @param DisplayConfigProvider $displayConfigProvider
     * @param ResolverInterface $localeResolver
     * @param CheckoutSession $checkoutSession
     * @param GeneralConfigProvider $generalConfigProvider
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly LayoutConfigProvider              $layoutConfigProvider,
        private readonly DisplayConfigProvider             $displayConfigProvider,
        private readonly ResolverInterface                 $localeResolver,
        private readonly CheckoutSession                   $checkoutSession,
        private readonly GeneralConfigProvider             $generalConfigProvider,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly ProductRepositoryInterface        $productRepository,
        private readonly StoreManagerInterface             $storeManager,
        private readonly LoggerInterface                   $logger
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->generalConfigProvider->isEnabled();
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
     * @return array
     */
    public function getLayoutConfig(): array
    {
        $variant = $this->layoutConfigProvider->getColorVariant();
        $darkMode = $this->layoutConfigProvider->isDarkModeEnabled();
        $maxWidth = $this->layoutConfigProvider->getMaxWidth();
        $frameStyle = $this->layoutConfigProvider->getFrameStyle();

        return [
            self::VARIANT => $variant,
            self::DARK_MODE => $darkMode,
            self::MAX_WIDTH => $maxWidth,
            self::FRAME_STYLE => $frameStyle
        ];
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
     * @return float|int
     */
    public function getCartItemsCount(): float|int
    {
        try {
            return $this->checkoutSession->getQuote()->getItemsSummaryQty();
        } catch (NoSuchEntityException|LocalizedException $e) {
            return 0;
        }
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

    public function hasNotAllowedProducts(): bool
    {
        try {
            $quote = $this->checkoutSession->getQuote();

            foreach ($quote->getAllVisibleItems() as $item) {
                if (in_array($item->getProduct()->getTypeId(), self::NOT_ALLOWED_PRODUCT_TYPES)) {
                    return true;
                }
            }

            return false;
        } catch (NoSuchEntityException|LocalizedException $e) {
            return false;
        }
    }
}
