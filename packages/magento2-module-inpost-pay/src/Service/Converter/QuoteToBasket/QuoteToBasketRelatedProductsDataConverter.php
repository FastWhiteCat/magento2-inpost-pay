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
use Zend_Db_Select;

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
        $cartProducts = [];
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $product = $quoteItem->getProduct();
            $cartProducts[(int)$product->getId()] = $product;
        }

        if ($cartProducts) {
            foreach ($this->getCrossSellProducts($cartProducts, (int)$quote->getStoreId()) as $crossSellProduct) {
                $crossSellData[] = $this->productToInPostProductDataConverter->convert($crossSellProduct, $websiteId);
            }
        }

        return $crossSellData;
    }

    /**
     * @param array $products
     * @param int $storeId
     * @return Product[]
     */
    private function getCrossSellProducts(array $products, int $storeId): array
    {
        $crossSellProducts = [];
        $product = current($products);
        $allProductIds = array_keys($products);
        if (!$product instanceof Product) {
            return $crossSellProducts;
        }

        $productLinkCollection = $product->getCrossSellProductCollection()
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setPositionOrder()
            ->addStoreFilter($storeId);

        $whereParts = [];
        $productIdWherePart = sprintf('AND (links.product_id in (%s))', (int)$product->getId());
        $productIdsWherePart = sprintf('AND (links.product_id in (%s))', implode(',', $allProductIds));
        foreach ($productLinkCollection->getSelect()->getPart(Zend_Db_Select::WHERE) as $wherePart) {
            if (str_contains($wherePart, $productIdWherePart)) {
                $whereParts[] = str_replace($productIdWherePart, $productIdsWherePart, $wherePart);
            } else {
                $whereParts[] = $wherePart;
            }
        }

        $productLinkCollection->getSelect()->setPart(Zend_Db_Select::WHERE, $whereParts);

        $sql2 = $productLinkCollection->getSelect()->__toString();
        $whereParts = $productLinkCollection->getSelect()->getPart(Zend_Db_Select::WHERE);
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
