<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\ApiConnector\IziApi\Basket\BasketFieldInterface as Basket;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use Magento\Quote\Model\Quote;

class QuoteToBasketProductsDataTransfer implements QuoteToBasketDataTransferInterface
{
    public function __construct(
        private readonly ProductInterfaceFactory $productFactory,
        private readonly PriceInterfaceFactory $priceFactory,
        private readonly ProductToInPostProductDataTransfer $productToInPostProductDataTransfer
    ) {
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $products = [];
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            /** @var ProductInterface $inPostProduct */
            $inPostProduct = $this->productFactory->create();
            $product = $quoteItem->getProduct();
            $websiteId = (int)$quote->getStore()->getWebsiteId();
            $qty = (float)$quoteItem->getQty();
            $this->productToInPostProductDataTransfer->transfer(
                $product,
                $inPostProduct,
                $websiteId,
                $qty
            );

            $priceExclTax = DecimalCalculator::round((float)$quoteItem->getPrice());
            $priceInclTax = DecimalCalculator::round((float)$quoteItem->getPriceInclTax());
            $taxValue = DecimalCalculator::sub($priceInclTax, $priceExclTax);

            /** @var PriceInterface $promoPrice */
            $promoPrice = $this->priceFactory->create();
            $promoPrice->setNet($priceExclTax);
            $promoPrice->setGross($priceInclTax);
            $promoPrice->setVat($taxValue);
            $inPostProduct->setPromoPrice($promoPrice);
            $products[] = $inPostProduct;
        }

        $basket->setProducts($products);
    }
}
