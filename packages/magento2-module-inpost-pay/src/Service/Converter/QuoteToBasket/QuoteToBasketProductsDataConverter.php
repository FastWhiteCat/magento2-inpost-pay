<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter\QuoteToBasket;

use InPost\InPostPay\Api\ApiConnector\IziApi\Basket\BasketFieldInterface as Basket;
use InPost\InPostPay\Api\Data\Converter\QuoteToBasketDataConverterInterface;
use InPost\InPostPay\Service\Converter\ProductToInPostProduct\ProductToInPostProductDataConverter;
use InPost\InPostPay\Api\ApiConnector\IziApi\Product\ProductFieldInterface as InPostProduct;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Quote\Model\Quote;

class QuoteToBasketProductsDataConverter implements QuoteToBasketDataConverterInterface
{
    public function __construct(
        private readonly ProductToInPostProductDataConverter $productToInPostProductDataConverter
    ) {
    }

    public function convert(Quote $quote): array
    {
        $productsData = [];
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $product = $quoteItem->getProduct();
            $productData = $this->productToInPostProductDataConverter->convert(
                $product,
                (int)$quote->getStore()->getWebsiteId(),
                (float)$quoteItem->getQty()
            );

            $priceExclTax = round((float)$quoteItem->getPrice(), 2);
            $priceInclTax = round((float)$quoteItem->getPriceInclTax(), 2);
            $productData[InPostProduct::PROMO_PRICE] = [
                Basket::NET => $priceExclTax,
                Basket::GROSS => $priceInclTax,
                Basket::VAT => $priceInclTax - $priceExclTax
            ];

            $productsData[] = $productData;
        }

        return $productsData;
    }
}
