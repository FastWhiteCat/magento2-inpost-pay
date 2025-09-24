<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\DataTransfer\OrderToInPostOrderDataTransferInterface;
use InPost\InPostPay\Model\Data\Merchant\Basket\Product as InPostPayProduct;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterfaceFactory;
use InPost\InPostPay\Service\Order\Item\OrderItemProductExtractor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class OrderToInPostOrderProductsDataTransfer implements OrderToInPostOrderDataTransferInterface
{
    public function __construct(
        private readonly ProductToInPostProductDataTransfer $productToInPostProductDataTransfer,
        private readonly ProductInterfaceFactory $productFactory,
        private readonly PriceInterfaceFactory $priceFactory,
        private readonly OrderItemProductExtractor $orderItemProductExtractor
    ) {
    }

    public function transfer(Order $order, OrderInterface $inPostOrder): void
    {
        $orderedProducts = [];
        $websiteId = (int)$order->getStore()->getWebsiteId();
        foreach ($order->getAllVisibleItems() as $orderItem) {
            if ($orderItem instanceof Item) {
                /** @var InPostPayProduct $inPostProduct */
                $inPostProduct = $this->productFactory->create();
                $this->transferProductData($orderItem, $inPostProduct, $websiteId, (float)$orderItem->getQtyOrdered());
                $inPostProduct->unsetData(ProductInterface::DELIVERY_PRODUCT);
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
            $options = [];
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $productOptions = $orderItem->getProductOptions();
                if ($productOptions && $productOptions['attributes_info']) {
                    $options = $productOptions['attributes_info'];
                }
            } elseif ($product->getTypeId() === Type::TYPE_BUNDLE) {
                $product->setData(ProductToInPostProductDataTransfer::BUNDLE_CHILD_PRODUCTS, []);

                $productOptions = $orderItem->getProductOptions();
                if ($productOptions && $productOptions['bundle_options']) {
                    foreach ($productOptions['bundle_options'] as $option) {
                        $options[] = [
                            'label' => $option['label'],
                            'value' => (float) $option['value'][0]['qty'] . ' x ' . $option['value'][0]['title']
                                . ' ' . DecimalCalculator::round((float)$option['value'][0]['price'])
                                . ' ' . $orderItem->getOrder()->getOrderCurrency()->getCurrencySymbol()
                        ];
                    }
                }
            }

            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $this->productToInPostProductDataTransfer->transfer(
                    $this->orderItemProductExtractor->extractProductFromOrderItem($orderItem),
                    $inPostProduct,
                    $websiteId,
                    $qty,
                    $options
                );
            } else {
                $this->productToInPostProductDataTransfer->transfer(
                    $product,
                    $inPostProduct,
                    $websiteId,
                    $qty,
                    $options
                );
            }

            $priceExclTax = DecimalCalculator::round((float)$orderItem->getPrice());
            $priceInclTax = DecimalCalculator::round((float)$orderItem->getPriceInclTax());
            $taxValue = DecimalCalculator::sub($priceInclTax, $priceExclTax);

            /** @var PriceInterface $basePrice */
            $basePrice = $this->priceFactory->create();
            $basePrice->setNet($priceExclTax);
            $basePrice->setGross($priceInclTax);
            $basePrice->setVat($taxValue);
            $inPostProduct->setBasePrice($basePrice);
        }
    }
}
