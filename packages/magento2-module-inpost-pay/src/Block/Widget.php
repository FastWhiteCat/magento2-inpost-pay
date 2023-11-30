<?php

declare(strict_types=1);

namespace InPost\InPostPay\Block;

use InPost\InPostPay\Provider\Config\LayoutConfigProvider;
use InPost\InPostPay\Provider\Config\DisplayConfigProvider;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class Widget extends Template
{
    /**
     * @param LayoutConfigProvider $layoutConfigProvider
     * @param DisplayConfigProvider $displayConfigProvider
     * @param ResolverInterface $localeResolver
     * @param CheckoutSession $checkoutSession
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        private readonly LayoutConfigProvider $layoutConfigProvider,
        private readonly DisplayConfigProvider $displayConfigProvider,
        ResolverInterface $localeResolver,
        CheckoutSession $checkoutSession,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
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
            'variant' => $variant,
            'darkMode' => $darkMode
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
    public function getProductId(): int
    {
       return (int)$this->getRequest()->getParam('id');
    }
}
