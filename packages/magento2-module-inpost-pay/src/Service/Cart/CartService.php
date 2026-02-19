<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Cart;

use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Exception\InvalidPromoCodeException;
use InPost\InPostPay\Observer\Quote\UpdateInPostBasketEventObserver;
use InPost\InPostPay\Service\CreateBasketNotice;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartService
{
    public const ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS = 'allow_inpost_pay_quote_remote_access';

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CouponManagementInterface $couponManagement,
        private readonly CreateBasketNotice $createBasketNotice,
        private readonly CartManagementInterface $cartManagement,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return Quote
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function initCart(): Quote
    {
        $quoteId = $this->cartManagement->createEmptyCart();

        return $this->cartRepository->get($quoteId); //@phpstan-ignore-line
    }

    /**
     * @param Quote $quote
     * @param int $productId
     * @param float $qty
     * @param bool $isQuoteItemId
     * @return void
     * @throws LocalizedException
     */
    public function addToCart(Quote $quote, int $productId, float $qty, bool $isQuoteItemId = false): void
    {
        $quoteId = (int)(is_scalar($quote->getId()) ? $quote->getId() : null);
        try {
            if (!$isQuoteItemId) {
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
            } else {
                $itemId = $productId;
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
     * @param bool $isQuoteItemId
     * @return void
     * @throws LocalizedException
     */
    public function removeFromCart(Quote $quote, int $productId, bool $isQuoteItemId = false): void
    {
        $quoteId = (int)(is_scalar($quote->getId()) ? $quote->getId() : null);
        try {
            $itemId = null;
            if ($isQuoteItemId) {
                $itemId = $productId;
            } else {
                $product = $this->productRepository->getById($productId, false, $quote->getStoreId());
                if ($product instanceof Product) {
                    $itemId = $this->getItemIdByProductFromCart($quote, $product);
                }
            }

            if ($itemId) {
                $quote->removeItem($itemId);
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
     * @throws InvalidPromoCodeException
     */
    public function applyPromo(Quote $quote, string $couponCode): void
    {
        $quoteId = $quote->getId();
        if (is_scalar($quoteId)) {
            $quoteId = (int)$quoteId;
            $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
            $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
            $appliedCoupon = $quote->getCouponCode();
            // @phpstan-ignore-next-line
            $basketId = (string)$quote->getData(InPostPayQuoteInterface::INPOST_BASKET_ID);

            try {
                $this->couponManagement->set($quoteId, $couponCode);

                if ($appliedCoupon && strtolower($appliedCoupon) === strtolower($couponCode)) {
                    $this->createBasketNotice->execute(
                        $basketId,
                        InPostPayBasketNoticeInterface::ATTENTION,
                        __('Coupon code is already activated')->render()
                    );
                } elseif ($appliedCoupon) {
                    $this->createBasketNotice->execute(
                        $basketId,
                        InPostPayBasketNoticeInterface::ATTENTION,
                        __('Coupon code has been updated')->render()
                    );
                } else {
                    $this->createBasketNotice->execute(
                        $basketId,
                        InPostPayBasketNoticeInterface::ATTENTION,
                        __('Coupon code has been applied')->render()
                    );
                }
            } catch (CouldNotSaveException | NoSuchEntityException $e) {
                $msg = 'Coupon code has been removed. The code entered is incorrect. Please enter the correct code';

                if ($appliedCoupon) {
                    $this->createBasketNotice->execute(
                        $basketId,
                        InPostPayBasketNoticeInterface::ATTENTION,
                        __($msg)->render()
                    );
                } else {
                    throw new InvalidPromoCodeException(__('Promo code "%1" is invalid.', $couponCode));
                }
            }
            $this->logger->debug(
                sprintf('Coupon code: %s has been applied to quote ID %s', $couponCode, $quoteId)
            );
        }
    }

    /**
     * @throws LocalizedException
     */
    public function removePromosFromQuote(Quote $quote): void
    {
        $quoteId = $quote->getId();
        if (is_scalar($quoteId)) {
            $quoteId = (int)$quoteId;
            $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
            $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
            $this->couponManagement->remove($quoteId);
            $this->logger->debug(
                sprintf('Coupon codes have been removed from quote ID %s', $quoteId)
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
        foreach ($quote->getAllVisibleItems() as $item) {
            /** @var Item $item */
            if ($item->getProduct()->getTypeId() === Configurable::TYPE_CODE
                && $itemId = $this->getItemIdByChildProductId($item, $product)
            ) {
                return $itemId;
            }

            if ($item->getProductId() === $product->getId()) {
                return ($item instanceof Item && is_scalar($item->getId()) ? (int)$item->getId() : null);
            }
        }

        return null;
    }

    private function getItemIdByChildProductId(Item $item, Product $product): ?int
    {
        $option = $item->getOptionByCode('simple_product');
        if ($option instanceof Option) {
            $productId = (int)$option->getProduct()->getId();
            if ((int)$product->getId() === $productId) {
                return ($item instanceof Item && is_scalar($item->getId()) ? (int)$item->getId() : null);
            }
        }

        return null;
    }
}
