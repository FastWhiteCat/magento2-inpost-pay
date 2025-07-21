<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Cart\Item;

use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote\Item;

class QuoteItemProductExtractor
{
    public function extractProductFromQuoteItem(Item $quoteItem): Product
    {
        $product = $quoteItem->getProduct();

        if ($quoteItem->getProductType() === Configurable::TYPE_CODE) {
            foreach ($quoteItem->getChildren() as $childItem) {
                $product = $childItem->getProduct();
                $product->setData(
                    ProductToInPostProductDataTransfer::CONFIGURABLE_PARENT_PRODUCT,
                    $quoteItem->getProduct()
                );

                break;
            }
        }

        return $product;
    }
}
