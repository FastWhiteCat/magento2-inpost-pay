<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\MerchantStoreInterfaceFactory as MerchantStoreFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\MerchantStoreInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\MerchantStore\CookieInterfaceFactory as CookieFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\MerchantStore\CookieInterface;
use InPost\InPostPay\Provider\Cart\Session\CartSessionCookieProvider;
use InPost\InPostPay\Provider\Config\SessionCookieConfigProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;

class QuoteToBasketMerchantStoreDataTransfer implements QuoteToBasketDataTransferInterface
{
    /**
     * @param InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository
     * @param CartSessionCookieProvider $cartSessionCookieProvider
     * @param MerchantStoreFactory $merchantStoreFactory
     * @param CookieFactory $cookieFactory
     * @param SessionCookieConfigProvider $sessionCookieConfigProvider
     */
    public function __construct(
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly CartSessionCookieProvider $cartSessionCookieProvider,
        private readonly MerchantStoreFactory $merchantStoreFactory,
        private readonly CookieFactory $cookieFactory,
        private readonly SessionCookieConfigProvider $sessionCookieConfigProvider
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $storeId = (int)$quote->getStoreId();

        if (!$this->sessionCookieConfigProvider->isSendingSessionCookieEnabled($storeId)) {
            return;
        }

        try {
            $quoteId = is_scalar($quote->getId()) ? (int)$quote->getId() : 0;
            $inPostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);
            $cartUrl = $this->sessionCookieConfigProvider->getCartUrl($storeId);
        } catch (NoSuchEntityException $e) {
            $inPostPayQuote = null;
            $cartUrl = null;
        }

        if ($cartUrl && $inPostPayQuote && $inPostPayQuote->getSessionCookie()) {
            /** @var MerchantStoreInterface $merchantStore */
            $merchantStore = $this->merchantStoreFactory->create();
            /** @var CookieInterface $cookie */
            $cookie = $this->cookieFactory->create();

            $cookie->setDomain($this->cartSessionCookieProvider->getCookieDomain());
            $cookie->setKey($this->cartSessionCookieProvider->getCookieSessionName());
            $cookie->setValue($inPostPayQuote->getSessionCookie());
            $cookie->setPath($this->cartSessionCookieProvider->getCookiePath());
            $cookie->setSameSite(strtoupper($this->cartSessionCookieProvider->getSameSite()));
            $cookie->setSecure($this->cartSessionCookieProvider->isSecure());
            $cookie->setHttpOnly($this->cartSessionCookieProvider->isHttpOnly());
            $cookie->setMaxAge($this->cartSessionCookieProvider->getCookieLifetime());
            $cookie->setPriority('MEDIUM');

            $merchantStore->setUrl($cartUrl);
            $merchantStore->setCookies([$cookie]);

            $basket->setMerchantStore($merchantStore);
        }
    }
}
