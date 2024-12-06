<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Item;

use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Sales\Model\Order\Item;

class OrderItemProductExtractor
{
    public function extractProductFromOrderItem(Item $orderItem): Product
    {
        /** @var Product $product */
        $product = $orderItem->getProduct();

        if ($orderItem->getProductType() === Configurable::TYPE_CODE) {
            foreach ($orderItem->getChildrenItems() as $childItem) {
                $product = $childItem->getProduct();
                $product->setData(
                    ProductToInPostProductDataTransfer::CONFIGURABLE_PARENT_PRODUCT,
                    $orderItem->getProduct()
                );

                break;
            }
        }

        return $product;
    }
}
