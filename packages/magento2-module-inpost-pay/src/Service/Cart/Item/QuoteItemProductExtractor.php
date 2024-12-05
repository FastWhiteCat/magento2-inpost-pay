<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Cart\Item;

use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;

class QuoteItemProductExtractor
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function extractProductFromQuoteItem(Item $quoteItem): Product
    {
        /** @var Product $product */
        $product = $quoteItem->getProduct();

        if ($quoteItem->getProductType() === Configurable::TYPE_CODE) {
            foreach ($quoteItem->getChildren() as $childItem) {
                try {
                    /** @var Product $product */
                    $product = $this->productRepository->get(
                        (string)$childItem->getProduct()->getSku(),
                        false,
                        (int)$quoteItem->getStoreId()
                    );

                    // @phpstan-ignore-next-line
                    $parentItemProduct = $childItem->getParentItem()->getProduct();
                    $product->setData(
                        ProductToInPostProductDataTransfer::CONFIGURABLE_PARENT_PRODUCT,
                        $parentItemProduct
                    );

                    break;
                } catch (NoSuchEntityException $e) {
                    continue;
                }
            }
        }

        return $product;
    }
}
