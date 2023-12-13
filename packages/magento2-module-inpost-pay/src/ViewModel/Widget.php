<?php

declare(strict_types=1);

namespace InPost\InPostPay\ViewModel;

use InPost\InPostPay\Provider\Config\LayoutConfigProvider;
use InPost\InPostPay\Provider\Config\DisplayConfigProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;

use Magento\Checkout\Model\Session as CheckoutSession;

class Widget implements ArgumentInterface
{
    private ResolverInterface $localeResolver;
    private CheckoutSession $checkoutSession;

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
        ResolverInterface                      $localeResolver,
        CheckoutSession                        $checkoutSession
    )
    {
        $this->localeResolver = $localeResolver;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return string
     */
    public function getCurrentLanguageCode(): string
    {
        $currentCode = $this->localeResolver->getLocale();

        return strstr($currentCode, '_', true);
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
            $quote = $this->checkoutSession->getQuote();

            return $quote->getItemsSummaryQty();
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
            if ($quote->getId()) {
                if ($quote->getCustomerIsGuest()) {
                    return $this->quoteIdToMaskedQuoteId->execute((int)$quote->getId());
                }

                return $quote->getId();
            }

            return "";
        } catch (NoSuchEntityException|LocalizedException $e) {
            return "";
        }
    }
}
