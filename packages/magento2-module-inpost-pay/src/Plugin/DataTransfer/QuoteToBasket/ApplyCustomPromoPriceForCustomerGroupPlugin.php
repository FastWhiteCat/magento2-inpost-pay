<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Provider\Config\OmnibusConfigProvider;
use InPost\InPostPay\Provider\Product\CustomProductPromoPriceProvider;
use InPost\InPostPay\Service\DataTransfer\QuoteToBasket\QuoteToBasketProductsDataTransfer;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;

class ApplyCustomPromoPriceForCustomerGroupPlugin
{
    /**
     * @param OmnibusConfigProvider $configProvider
     * @param CustomProductPromoPriceProvider $customProductPromoPriceProvider
     */
    public function __construct(
        private readonly OmnibusConfigProvider $configProvider,
        private readonly CustomProductPromoPriceProvider $customProductPromoPriceProvider,
    ) {
    }

    /**
     * @param QuoteToBasketProductsDataTransfer $subject
     * @param $result
     * @param Quote $quote
     * @param BasketInterface $basket
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterTransfer( //@phpstan-ignore-line
        QuoteToBasketProductsDataTransfer $subject,
        $result,
        Quote $quote,
        BasketInterface $basket
    ): void {
        $storeId = $quote->getStoreId();
        $customPromoPriceAttribute = $this->configProvider->getCustomProductPromoPriceAttributeCode();
        $customerGroups = $this->configProvider->getCustomProductPromoPriceCustomerGroups($storeId);

        if (!$this->configProvider->isCustomPromoPriceForSpecificCustomerGroupEnabled($storeId)
            || $customPromoPriceAttribute === null
            || !in_array($quote->getCustomerGroupId(), $customerGroups)
        ) {
            return;
        }

        foreach ($basket->getProducts() as $inPostPayProduct) {
            $product = $this->extractProductFromQuoteById((int)$inPostPayProduct->getProductId(), $quote);

            if ($product === null) {
                continue;
            }

            try {
                $customPromoPrice = $this->customProductPromoPriceProvider->getCustomPromoPrice(
                    $product,
                    $customPromoPriceAttribute
                );
            } catch (NoSuchEntityException $e) {
                $customPromoPrice = null;
            }

            if ($customPromoPrice) {
                $inPostPayProduct->setPromoPrice($customPromoPrice);
            }
        }
    }

    /**
     * @param int $productId
     * @param Quote $quote
     * @return Product|null
     */
    private function extractProductFromQuoteById(int $productId, Quote $quote): ?Product
    {
        $product = null;

        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $quoteProductId = (int)$quoteItem->getProduct()->getId();

            if ($quoteProductId === $productId) {
                $product = $quoteItem->getProduct();
                break;
            }
        }

        return $product;
    }
}
