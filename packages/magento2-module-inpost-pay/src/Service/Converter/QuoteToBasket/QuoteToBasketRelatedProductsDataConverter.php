<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter\QuoteToBasket;

use InPost\InPostPay\Api\Data\Converter\QuoteToBasketDataConverterInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection as ProductLinkCollection;
use InPost\InPostPay\Service\Converter\ProductToInPostProduct\ProductToInPostProductDataConverter;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Quote\Model\Quote;

class QuoteToBasketRelatedProductsDataConverter implements QuoteToBasketDataConverterInterface
{
    public function __construct(
        private readonly ProductToInPostProductDataConverter $productToInPostProductDataConverter,
        private readonly CatalogConfig $catalogConfig
    ) {
    }

    public function convert(Quote $quote): array
    {
        $crossSellData = [];
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $product = $quoteItem->getProduct();
            foreach ($this->getCrossSellProducts($product, (int)$quote->getStoreId()) as $crossSellProduct) {
                $crossSellData[] = $this->productToInPostProductDataConverter->convert($crossSellProduct, $websiteId);
            }
        }

        return $crossSellData;
    }

    /**
     * @param Product $product
     * @param int $storeId
     * @return Product[]
     */
    private function getCrossSellProducts(Product $product, int $storeId): array
    {
        $crossSellProducts = [];
        /** @var ProductLinkCollection $productLinkCollection */
        $productLinkCollection = $product->getCrossSellProductCollection()
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setPositionOrder()
            ->addStoreFilter($storeId)
            ->load();

        foreach ($productLinkCollection as $crossSellProduct) {
            if ($crossSellProduct instanceof Product && $crossSellProduct->getTypeId() === Type::TYPE_SIMPLE) {
                // @phpstan-ignore-next-line
                $crossSellProduct->setDoNotUseCategoryId(true);
                $crossSellProducts[] = $crossSellProduct;
            }
        }

        return $crossSellProducts;
    }
}
