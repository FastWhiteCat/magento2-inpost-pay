<?php

declare(strict_types=1);

namespace InPost\InPostPayCommerce\Service\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use InPost\Restrictions\Provider\RestrictedProductIdsProvider;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Link\Collection as ProductLinkCollection;
use Magento\Catalog\Model\ResourceModel\Product\Link\CollectionFactory as ProductLinkCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory as ProductCollectionFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterfaceFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteToBasketRelatedProductsDataTransfer implements QuoteToBasketDataTransferInterface
{
    private const MAX_CROSS_SELL_PRODUCTS = 10;

    public function __construct(
        private readonly ProductInterfaceFactory $productFactory,
        private readonly ProductCollectionFactory $productCollectionFactory,
        private readonly ProductLinkCollectionFactory $productLinkCollectionFactory,
        private readonly ProductToInPostProductDataTransfer $productToInPostProductDataTransfer,
        private readonly RestrictedProductIdsProvider $restrictedProductIdsProvider,
        private readonly CatalogConfig $catalogConfig,
        private readonly StatusFactory $stockStatusFactory
    ) {
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $inPostCrossSellProducts = [];
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        $cartProductIds = [];
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $cartProductIds[] = (int)$quoteItem->getProduct()->getId();
        }

        if ($cartProductIds) {
            foreach ($this->getCrossSellProducts($cartProductIds, $quote->getStore()) as $crossSellProduct) {
                $inPostCrossSellProduct = $this->productFactory->create();
                $this->productToInPostProductDataTransfer->transfer(
                    $crossSellProduct,
                    $inPostCrossSellProduct,
                    $websiteId
                );
                $inPostCrossSellProducts[] = $inPostCrossSellProduct;
            }
        }

        $basket->setRelatedProducts($inPostCrossSellProducts);
    }

    /**
     * @param int[] $productIds
     * @param Store $store
     * @return Product[]
     */
    private function getCrossSellProducts(array $productIds, Store $store): array
    {
        $websiteId = (int)$store->getWebsiteId();
        $storeId = (int)$store->getId();
        $crossSellProducts = [];
        $linkedProductIds = $this->getCrossLinkedProductIds($productIds);
        if ($linkedProductIds) {
            /** @var ProductCollection $productsCollection */
            $productsCollection = $this->productCollectionFactory->create();
            $productsCollection->addAttributeToSelect($this->catalogConfig->getProductAttributes())
                ->setPositionOrder()
                ->addStoreFilter($storeId)
                ->addFieldToFilter(
                    'entity_id',
                    ['in' => $linkedProductIds]
                )->addFieldToFilter(
                    'entity_id',
                    ['nin' => $this->restrictedProductIdsProvider->getList(
                        $websiteId
                    )]
                );

            $stockStatusResource = $this->stockStatusFactory->create();
            $stockStatusResource->addStockDataToCollection($productsCollection, true);
            $productsCollection->setFlag('has_stock_status_filter', true);

            foreach ($productsCollection->load() as $crossSellProduct) {
                if ($crossSellProduct instanceof Product
                    && $crossSellProduct->getTypeId() === Type::TYPE_SIMPLE
                    && $crossSellProduct->isVisibleInCatalog()
                ) {
                    $crossSellProducts[] = $crossSellProduct;
                    if (count($crossSellProducts) >= self::MAX_CROSS_SELL_PRODUCTS) {
                        break;
                    }
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
            ->addFieldToFilter('linked_product_id', ['nin' => $productIds])
            ->load();

        foreach ($productLinkCollection as $link) {
            if ($link instanceof Link) {
                $linkedProductIds[] = (int)$link->getLinkedProductId();
            }
        }

        return $linkedProductIds;
    }
}
