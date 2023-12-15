<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Cart;

use InPost\InPostPay\Observer\Quote\UpdateInPostBasketEventObserver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class CartService
{
    public const ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS = 'allow_inpost_pay_quote_remote_access';

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CouponManagementInterface $couponManagement,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Quote $quote
     * @param int $productId
     * @param float $qty
     * @return void
     * @throws LocalizedException
     */
    public function addToCart(Quote $quote, int $productId, float $qty): void
    {
        try {
            $quoteId = (int)(is_scalar($quote->getId()) ? $quote->getId() : null);
            $product = $this->productRepository->getById($productId, false, $quote->getStoreId());
            if ($product instanceof Product) {
                $quote->addProduct($product, (float)$qty);
                $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
                $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
                // @phpstan-ignore-next-line
                $quote->setTotalsCollectedFlag(false);
                $quote->collectTotals();
                $this->cartRepository->save($quote);
                $this->logger->debug(
                    sprintf('Product ID %s in qty %s has been added to quote ID %s', $productId, $qty, $quoteId)
                );
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw new LocalizedException(
                __('Could not add product ID %1 in quantity of %2 to cart.', (string)$productId, (string)$qty)
            );
        }
    }

    /**
     * @throws LocalizedException
     */
    public function applyPromo(Quote $quote, string $couponCode): void
    {
        if (is_scalar($quote->getId())) {
            $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
            $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
            $this->couponManagement->set((int)$quote->getId(), $couponCode);
            $this->logger->debug(
                sprintf('Coupon code: %s has been applied to quote ID %s', $couponCode, (int)$quote->getId())
            );
        }
    }
}
