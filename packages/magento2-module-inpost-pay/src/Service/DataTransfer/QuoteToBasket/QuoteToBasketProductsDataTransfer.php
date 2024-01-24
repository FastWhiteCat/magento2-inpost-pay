<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use InPost\InPostPay\Service\CreateBasketNotice;
use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Provider\RestrictedProductIdsProvider;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteToBasketProductsDataTransfer implements QuoteToBasketDataTransferInterface
{
    public function __construct(
        private readonly ProductInterfaceFactory $productFactory,
        private readonly PriceInterfaceFactory $priceFactory,
        private readonly ProductToInPostProductDataTransfer $productToInPostProductDataTransfer,
        private readonly RestrictedProductIdsProvider $restrictedProductIdsProvider,
        private readonly CreateBasketNotice $createBasketNotice
    ) {
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $products = [];
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            /** @var ProductInterface $inPostProduct */
            /** @var Item $quoteItem */
            $inPostProduct = $this->productFactory->create();
            $product = $quoteItem->getProduct();
            $websiteId = (int)$quote->getStore()->getWebsiteId();
            $qty = (float)$quoteItem->getQty();
            $options = [];

            if ($quoteItem->getProduct()->getTypeId() == Configurable::TYPE_CODE) {
                $option = $quoteItem->getOptionByCode('simple_product');
                if ($option instanceof Option) {
                    $product->setData('simple_product_id', (string)$option->getProduct()->getId());

                }
                // @phpstan-ignore-next-line
                $options = $quoteItem->getProduct()->getTypeInstance()->getSelectedAttributesInfo($product);
            }

            $productId = (int)$product->getId();
            if ($this->isRestricted($productId, $websiteId)) {
                $noticePhrase = __(
                    'Product "%1" is not available for InPost Pay.',
                    mb_substr((string)$product->getName(), 0, 50)
                );

                $this->addBasketNotice(
                    (string)$basket->getBasketId(),
                    $noticePhrase->render()
                );

                continue;
            }

            $this->productToInPostProductDataTransfer->transfer(
                $product,
                $inPostProduct,
                $websiteId,
                $qty,
                $options
            );

            $priceExclTax = DecimalCalculator::round((float)$quoteItem->getPrice());
            $priceInclTax = DecimalCalculator::round((float)$quoteItem->getPriceInclTax());
            $taxValue = DecimalCalculator::sub($priceInclTax, $priceExclTax);

            /** @var PriceInterface $promoPrice */
            $promoPrice = $this->priceFactory->create();
            $promoPrice->setNet($priceExclTax);
            $promoPrice->setGross($priceInclTax);
            $promoPrice->setVat($taxValue);
            $inPostProduct->setPromoPrice($promoPrice);
            $products[] = $inPostProduct;
        }

        $basket->setProducts($products);
    }

    private function isRestricted(int $productId, int $websiteId): bool
    {
        $restrictedProductIds = $this->restrictedProductIdsProvider->getList(
            $websiteId,
            RestrictionsRuleInterface::APPLIES_TO_PAYMENT
        );

        return in_array($productId, $restrictedProductIds);
    }

    private function addBasketNotice(string $basketId, string $message): void
    {
        $this->createBasketNotice->execute(
            $basketId,
            InPostPayBasketNoticeInterface::ATTENTION,
            $message
        );
    }
}
