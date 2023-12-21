<?php

declare(strict_types=1);

namespace InPost\InPostPay\ViewModel;

use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use InPost\InPostPay\Provider\Config\LayoutConfigProvider;
use InPost\InPostPay\Provider\Config\DisplayConfigProvider;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;

use Magento\Checkout\Model\Session as CheckoutSession;

class Widget implements ArgumentInterface
{
    private const VARIANT = 'variant';
    private const DARK_MODE = 'darkMode';

    /**
     * @param LayoutConfigProvider $layoutConfigProvider
     * @param DisplayConfigProvider $displayConfigProvider
     * @param ResolverInterface $localeResolver
     * @param CheckoutSession $checkoutSession
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     */
    public function __construct(
        private readonly LayoutConfigProvider $layoutConfigProvider,
        private readonly DisplayConfigProvider $displayConfigProvider,
        private readonly QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        private readonly ResolverInterface $localeResolver,
        private readonly CheckoutSession $checkoutSession,
        private readonly GeneralConfigProvider $generalConfigProvider,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository
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

        return [
            self::VARIANT => $variant,
            self::DARK_MODE => $darkMode
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

    /**
     * @return string
     */
    public function getQuoteId(): string
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            $quoteId = is_scalar($quote->getId()) ? (int)$quote->getId() : null;
            if ($quoteId) {
                if ($quote->getCustomerIsGuest()) {
                    return $this->quoteIdToMaskedQuoteId->execute($quoteId);
                }

                return (string)$quoteId;
            }

            return "";
        } catch (NoSuchEntityException|LocalizedException $e) {
            return "";
        }
    }

    public function isInPostPayOrder():bool
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
}
