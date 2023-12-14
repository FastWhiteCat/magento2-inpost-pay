<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Link\Collection as ProductLinkCollection;
use Magento\Catalog\Model\ResourceModel\Product\Link\CollectionFactory as ProductLinkCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory as ProductCollectionFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterfaceFactory;
use Magento\Quote\Model\Quote;

class QuoteToBasketRelatedProductsDataTransfer implements QuoteToBasketDataTransferInterface
{
    public function __construct(
        private readonly ProductInterfaceFactory $productFactory,
        private readonly ProductCollectionFactory $productCollectionFactory,
        private readonly ProductLinkCollectionFactory $productLinkCollectionFactory,
        private readonly ProductToInPostProductDataTransfer $productToInPostProductDataTransfer,
        private readonly CatalogConfig $catalogConfig
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
            foreach ($this->getCrossSellProducts($cartProductIds, (int)$quote->getStoreId()) as $crossSellProduct) {
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
