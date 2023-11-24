<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Cart;

use InPost\InPostPay\Observer\Quote\UpdateInPostBasketEventObserver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\CouponFactory;
use Psr\Log\LoggerInterface;

class CartService
{
    public const ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS = 'allow_inpost_pay_quote_remote_access';

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly RequestQuantityProcessor $requestQuantityProcessor,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CouponFactory $couponFactory,
        private readonly LocaleResolver $localeResolver,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Quote $quote
     * @param int $productId
     * @param int|float $qty
     * @return void
     * @throws LocalizedException
     */
    public function addToCart(Quote $quote, int $productId, int|float $qty): void
    {
        try {
            $quoteId = (int)$quote->getId();
            $product = $this->productRepository->getById($productId);
            $filter = new LocalizedToNormalized(['locale' => $this->localeResolver->getLocale()]);
            $qty = $filter->filter((string)$this->requestQuantityProcessor->prepareQuantity($qty));
            $quote->addProduct($product, $qty);
            $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
            $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
            // @phpstan-ignore-next-line
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
            $this->cartRepository->save($quote);
            $this->logger->debug(
                sprintf('Product ID %s in qty %s has been added to quote ID %s', $productId, $qty, $quoteId)
            );
        } catch (LocalizedException $e) {
            $this->logger->error($e);

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
        $codeLength = strlen($couponCode);
        if (!$codeLength) {
            return;
        }

        try {
            $isCodeLengthValid = $codeLength <= Cart::COUPON_CODE_MAX_LENGTH;
            $itemsCount = $quote->getItemsCount();
            if ($itemsCount) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->setCouponCode($isCodeLengthValid ? $couponCode : '');
                // @phpstan-ignore-next-line
                $quote->setTotalsCollectedFlag(false);
                $quote->collectTotals();
                $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
                $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
                $this->cartRepository->save($quote);
            }
            $coupon = $this->couponFactory->create();
            $coupon->load($couponCode, 'code');
            if (!$quote->getItemsCount()) {
                if ($isCodeLengthValid && $coupon->getId()) {
                    $quote->setCouponCode($couponCode);
                    $this->logger->debug(
                        sprintf('Coupon code %s has been applied to quote ID %s', $couponCode, (int)$quote->getId())
                    );
                } else {
                    throw new LocalizedException(__('The coupon code "%1" is not valid.', $couponCode));
                }
            } else {
                if ($isCodeLengthValid && $coupon->getId() && $couponCode == $quote->getCouponCode()) {
                    $this->logger->debug(
                        sprintf('Coupon code %s has been applied to quote ID %s', $couponCode, (int)$quote->getId())
                    );
                } else {
                    throw new LocalizedException(__('The coupon code "%1" is not valid.', $couponCode));
                }
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }
}
