<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Cart;

use InPost\InPostPay\Observer\Quote\UpdateInPostBasketEventObserver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class CartService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly RequestQuantityProcessor $requestQuantityProcessor,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly LocaleResolver $localeResolver,
        private readonly LoggerInterface $logger
    ) {
    }

    public function addToCart(Quote $quote, int $productId, int|float $qty): void
    {
        try {
            $product = $this->productRepository->getById($productId);
            $filter = new LocalizedToNormalized(['locale' => $this->localeResolver->getLocale()]);
            $qty = $filter->filter($this->requestQuantityProcessor->prepareQuantity($qty));
            $quote->addProduct($product, $qty);
            $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
            $this->cartRepository->save($quote);
        } catch (LocalizedException $e) {
            $this->logger->error($e);
        }
    }

    public function applyPromo(Quote $quote, string $coupon): void
    {
        return;
    }
}
