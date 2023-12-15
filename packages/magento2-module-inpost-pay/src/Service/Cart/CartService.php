<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Cart;

use InPost\InPostPay\Observer\Quote\UpdateInPostBasketEventObserver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
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
        $quoteId = (int)(is_scalar($quote->getId()) ? $quote->getId() : null);
        try {
            $product = $this->productRepository->getById($productId, false, $quote->getStoreId());
            if (!$product instanceof Product) {
                throw new NoSuchEntityException(__('Product ID: %1 not found.', $productId));
            }

            $itemId = $this->getItemIdByProductFromCart($quote, $product);
            if ($itemId === null) {
                $quote->addProduct($product, (float)$qty);
            } else {
                $quoteItem = $quote->getItemById($itemId);
                if ($quoteItem) {
                    $quoteItem->setQty($qty);
                }
            }

            $this->applyQuoteChanges($quote);

            $this->logger->debug(
                sprintf('Product ID %s in qty %s has been added to quote ID %s', $productId, $qty, $quoteId)
            );
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw new LocalizedException(
                __('Could not add product ID %1 in quantity of %2 to cart.', (string)$productId, (string)$qty)
            );
        }
    }

    /**
     * @param Quote $quote
     * @param int $productId
     * @return void
     * @throws LocalizedException
     */
    public function removeFromCart(Quote $quote, int $productId): void
    {
        $quoteId = (int)(is_scalar($quote->getId()) ? $quote->getId() : null);
        try {
            $product = $this->productRepository->getById($productId, false, $quote->getStoreId());
            if ($product instanceof Product) {
                $itemId = $this->getItemIdByProductFromCart($quote, $product);
                if ($itemId) {
                    $quote->removeItem($itemId);
                }
                $this->applyQuoteChanges($quote);

                $this->logger->debug(
                    sprintf('Product ID %s has been removed from quote ID %s', $productId, $quoteId)
                );
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw new LocalizedException(
                __('Could not remove product ID %1 from quote ID: %s.', (string)$productId, (string)$quoteId)
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

    /**
     * @throws LocalizedException
     */
    public function removePromosFromQuote(Quote $quote): void
    {
        if (is_scalar($quote->getId())) {
            $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
            $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
            $this->couponManagement->remove((int)$quote->getId());
            $this->logger->debug(
                sprintf('Coupon codes have been removed from quote ID %s', (int)$quote->getId())
            );
        }
    }

    private function applyQuoteChanges(Quote $quote): void
    {
        $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
        $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
        // @phpstan-ignore-next-line
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $this->cartRepository->save($quote);
    }

    private function getItemIdByProductFromCart(Quote $quote, Product $product): ?int
    {
        $item = $quote->getItemByProduct($product);
        if ($item instanceof Item) {
            return (is_scalar($item->getId()) ? (int)$item->getId() : null);
        }

        return null;
    }
}
