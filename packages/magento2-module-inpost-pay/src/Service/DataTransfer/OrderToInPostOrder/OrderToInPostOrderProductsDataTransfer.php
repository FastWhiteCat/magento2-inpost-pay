<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\DataTransfer\OrderToInPostOrderDataTransferInterface;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;

class OrderToInPostOrderProductsDataTransfer implements OrderToInPostOrderDataTransferInterface
{
    public function __construct(
        private readonly ProductToInPostProductDataTransfer $productToInPostProductDataTransfer,
        private readonly ProductInterfaceFactory $productFactory,
        private readonly PriceInterfaceFactory $priceFactory
    ) {
    }

    public function transfer(Order $order, OrderInterface $inPostOrder): void
    {
        $orderedProducts = [];
        $websiteId = (int)$order->getStore()->getWebsiteId();
        foreach ($order->getAllVisibleItems() as $orderItem) {
            if ($orderItem instanceof Item) {
                $inPostProduct = $this->productFactory->create();
                $this->transferProductData($orderItem, $inPostProduct, $websiteId, (float)$orderItem->getQtyOrdered());
                $orderedProducts[] = $inPostProduct;
            }
        }

        $inPostOrder->setProducts($orderedProducts);
    }

    private function transferProductData(
        Item $orderItem,
        ProductInterface $inPostProduct,
        int $websiteId,
        float $qty
    ): void {
        $product = $orderItem->getProduct();
        if ($product instanceof  Product) {
            $this->productToInPostProductDataTransfer->transfer($product, $inPostProduct, $websiteId, $qty);

            $priceExclTax = DecimalCalculator::round((float)$orderItem->getPrice());
            $priceInclTax = DecimalCalculator::round((float)$orderItem->getPriceInclTax());
            $taxValue = DecimalCalculator::sub($priceInclTax, $priceExclTax);

            /** @var PriceInterface $promoPrice */
            $promoPrice = $this->priceFactory->create();
            $promoPrice->setNet($priceExclTax);
            $promoPrice->setGross($priceInclTax);
            $promoPrice->setVat($taxValue);
            $inPostProduct->setPromoPrice($promoPrice);
        }
    }
}
