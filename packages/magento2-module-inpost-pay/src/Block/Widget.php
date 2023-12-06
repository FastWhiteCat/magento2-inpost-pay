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
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Checkout\Model\Session as CheckoutSession;

class Widget extends Template
{
    /**
     * @param LayoutConfigProvider $layoutConfigProvider
     * @param DisplayConfigProvider $displayConfigProvider
     * @param ResolverInterface $localeResolver
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        private readonly LayoutConfigProvider $layoutConfigProvider,
        private readonly DisplayConfigProvider $displayConfigProvider,
        ResolverInterface $localeResolver,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->localeResolver = $localeResolver;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
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
    /**
     * @return string
     */
    public function getQuoteId(): string
    {
        try {
            if ($this->checkoutSession->getQuote()->getId()) {
                $quote = $this->quoteRepository->get($this->checkoutSession->getQuote()->getId());

                $quoteData = $quote->toArray();

                if (!$quote->getCustomer()->getId()) {
                    /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
                    $quoteIdMask = $this->quoteIdMaskFactory->create();
                    return $quoteIdMask->load(
                        $this->checkoutSession->getQuote()->getId(),
                        'quote_id'
                    )->getMaskedId();
                }

                return $quoteData['entity_id'];
            }

            return "";
        } catch (NoSuchEntityException|LocalizedException $e) {
            return "";
        }
    }
    public function getProductId(): int
    {
       return (int)$this->getRequest()->getParam('id');
    }
}
