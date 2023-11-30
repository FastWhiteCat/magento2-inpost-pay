<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter\QuoteToBasket;

use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\Link\CollectionFactory as ProductLinkCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link\Collection as ProductLinkCollection;
use InPost\InPostPay\Api\Data\Converter\QuoteToBasketDataConverterInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use InPost\InPostPay\Service\Converter\ProductToInPostProduct\ProductToInPostProductDataConverter;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Quote\Model\Quote;

class QuoteToBasketRelatedProductsDataConverter implements QuoteToBasketDataConverterInterface
{
    private const PRODUCT_LINK_TABLE_NAME = 'catalog_product_link';

    public function __construct(
        private readonly ProductCollectionFactory $productCollectionFactory,
        private readonly ProductLinkCollectionFactory $productLinkCollectionFactory,
        private readonly ProductToInPostProductDataConverter $productToInPostProductDataConverter,
        private readonly CatalogConfig $catalogConfig
    ) {
    }

    public function convert(Quote $quote): array
    {
        $crossSellData = [];
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        $cartProductIds = [];
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $cartProductIds[] = (int)$quoteItem->getProduct()->getId();
        }

        if ($cartProductIds) {
            foreach ($this->getCrossSellProducts($cartProductIds, (int)$quote->getStoreId()) as $crossSellProduct) {
                $crossSellData[] = $this->productToInPostProductDataConverter->convert($crossSellProduct, $websiteId);
            }
        }

        return $crossSellData;
    }

    /**
     * @param int[] $productIds
     * @param int $storeId
     * @return Product[]
     */
    private function getCrossSellProducts(array $productIds, int $storeId): array
    {
        $crossSellProducts = [];
        $linkedProductIds = $this->getCrossLinkedProductIds($productIds);
        if ($linkedProductIds) {
            /** @var ProductCollection $productsCollection */
            $productsCollection = $this->productCollectionFactory->create();
            $productsCollection->addAttributeToSelect($this->catalogConfig->getProductAttributes())
                ->setPositionOrder()
                ->addStoreFilter($storeId)
                ->addFieldToFilter(
                    $productsCollection->getProductEntityMetadata()->getLinkField(),
                    ['in' => $linkedProductIds]
                );

            foreach ($productsCollection->load() as $crossSellProduct) {
                if ($crossSellProduct instanceof Product && $crossSellProduct->getTypeId() === Type::TYPE_SIMPLE) {
                    $crossSellProducts[] = $crossSellProduct;
                }
            }
        }

        return $crossSellProducts;
    }

    /**
     * @param array $productIds
     * @return int[]
     */
    private function getCrossLinkedProductIds(array $productIds): array
    {
        $linkedProductIds = [];
        /** @var ProductLinkCollection $productLinkCollection */
        $productLinkCollection = $this->productLinkCollectionFactory->create()
            ->addFieldToFilter('link_type_id', ['eq' => Link::LINK_TYPE_CROSSSELL])
            ->addFieldToFilter('product_id', ['in' => $productIds])
            ->load();

        foreach ($productLinkCollection as $link) {
            if ($link instanceof Link) {
                $linkedProductIds[] = (int)$link->getLinkedProductId();
            }
        }

        return $linkedProductIds;
    }
}
