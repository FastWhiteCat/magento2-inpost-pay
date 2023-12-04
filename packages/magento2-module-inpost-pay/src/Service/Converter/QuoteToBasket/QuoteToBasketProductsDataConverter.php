<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter\QuoteToBasket;

use InPost\InPostPay\Api\ApiConnector\IziApi\Basket\BasketFieldInterface as Basket;
use InPost\InPostPay\Api\Data\Converter\QuoteToBasketDataConverterInterface;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use InPost\InPostPay\Service\Converter\ProductToInPostProduct\ProductToInPostProductDataConverter;
use InPost\InPostPay\Api\ApiConnector\IziApi\Product\ProductFieldInterface as InPostProduct;
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

            $priceExclTax = DecimalCalculator::round((float)$quoteItem->getPrice());
            $priceInclTax = DecimalCalculator::round((float)$quoteItem->getPriceInclTax());
            $taxValue = DecimalCalculator::sub($priceInclTax, $priceExclTax);
            $productData[InPostProduct::PROMO_PRICE] = [
                Basket::NET => $priceExclTax,
                Basket::GROSS => $priceInclTax,
                Basket::VAT => $taxValue
            ];

            $productsData[] = $productData;
        }

        return $productsData;
    }
}
