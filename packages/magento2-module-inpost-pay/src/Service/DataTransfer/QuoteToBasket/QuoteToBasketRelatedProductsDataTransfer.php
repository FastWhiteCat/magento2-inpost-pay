<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Provider\Product\Attribute\InPostPayProductAttributesProvider;
use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use InPost\Restrictions\Provider\RestrictedProductIdsProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Link\Collection as ProductLinkCollection;
use Magento\Catalog\Model\ResourceModel\Product\Link\CollectionFactory as ProductLinkCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory as ProductCollectionFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterfaceFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Framework\Exception\LocalizedException;
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
        private readonly InPostPayProductAttributesProvider $inPostPayProductAttributesProvider,
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
                if (!$crossSellProduct instanceof Product) {
                    continue;
                }

                $inPostCrossSellProduct = $this->productFactory->create();
                $this->productToInPostProductDataTransfer->transfer(
                    $crossSellProduct,
                    $inPostCrossSellProduct,
                    $websiteId,
                    null,
                    [],
                    [],
                    true
                );
                $inPostCrossSellProducts[] = $inPostCrossSellProduct;
            }
        }

        $basket->setRelatedProducts($inPostCrossSellProducts);
    }

    /**
     * @param int[] $productIds
     * @param Store $store
     * @return array
     * @throws LocalizedException
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
            $restrictedProductIds = $this->restrictedProductIdsProvider->getList($websiteId);
            $productsCollection->addAttributeToSelect($this->prepareProductAttributesList($storeId))
                ->setPositionOrder()
                ->addStoreFilter($storeId)
                ->addAttributeToFilter(
                    ProductInterface::TYPE_ID,
                    ['in' =>
                        [
                            Type::TYPE_SIMPLE,
                            Type::TYPE_VIRTUAL,
                            DownloadableType::TYPE_DOWNLOADABLE
                        ]
                    ]
                )
                ->addAttributeToFilter('status', ['eq' => Status::STATUS_ENABLED])
                ->setVisibility([Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH])
                ->addFieldToFilter(
                    $productsCollection->getProductEntityMetadata()->getLinkField(),
                    ['in' => $linkedProductIds]
                )
                ->setPageSize(self::MAX_CROSS_SELL_PRODUCTS);

            if (!empty($restrictedProductIds)) {
                $productsCollection->addFieldToFilter(
                    $productsCollection->getProductEntityMetadata()->getLinkField(),
                    ['nin' => $restrictedProductIds]
                );
            }

            $stockStatusResource = $this->stockStatusFactory->create();
            $stockStatusResource->addStockDataToCollection($productsCollection, true);
            $productsCollection->setFlag('has_stock_status_filter', true);
            $productsCollection->load();
            $productsCollection->addMediaGalleryData();
            $crossSellProducts = $productsCollection->getItems();
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

    private function prepareProductAttributesList(int $storeId): array
    {
        return array_unique(
            array_merge(
                $this->catalogConfig->getProductAttributes(),
                $this->inPostPayProductAttributesProvider->getProductAttributeCodes($storeId)
            )
        );
    }
}
