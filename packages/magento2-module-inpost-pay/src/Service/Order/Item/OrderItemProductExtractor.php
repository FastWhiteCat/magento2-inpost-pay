<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Item;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Item;

class OrderItemProductExtractor
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function extractProductFromOrderItem(Item $quoteItem): Product
    {
        /** @var Product $product */
        $product = $quoteItem->getProduct();

        if ($quoteItem->getProductType() === Configurable::TYPE_CODE) {
            foreach ($quoteItem->getChildrenItems() as $childItem) {
                try {
                    /** @var Product $product */
                    $product = $this->productRepository->get(
                        (string)$childItem->getProduct()->getSku(),
                        false,
                        (int)$quoteItem->getStoreId()
                    );

                    // @phpstan-ignore-next-line
                    $parentItemProductId = (int)$childItem->getParentItem()->getProduct()->getId();
                    $product->setData('configurable_product_id', $parentItemProductId);

                    break;
                } catch (NoSuchEntityException $e) {
                    continue;
                }
            }
        }

        return $product;
    }
}
