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
use InPost\InPostPay\Provider\Product\OmnibusProductLowestPriceProvider;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use InPost\InPostPay\Service\Cart\Item\QuoteItemProductExtractor;
use InPost\InPostPay\Service\CreateBasketNotice;
use InPost\InPostPay\Service\PrepareQuoteProductsQuantity;
use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use InPost\Restrictions\Provider\RestrictedProductIdsProvider;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteToBasketProductsDataTransfer implements QuoteToBasketDataTransferInterface
{
    public function __construct(
        private readonly ProductInterfaceFactory $productFactory,
        private readonly PriceInterfaceFactory $priceFactory,
        private readonly ProductToInPostProductDataTransfer $productToInPostProductDataTransfer,
        private readonly RestrictedProductIdsProvider $restrictedProductIdsProvider,
        private readonly CreateBasketNotice $createBasketNotice,
        private readonly PrepareQuoteProductsQuantity $prepareQuoteProductsQuantity,
        private readonly QuoteItemProductExtractor $quoteItemProductExtractor,
        private readonly OmnibusProductLowestPriceProvider $omnibusProductLowestPriceProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $products = [];
        $quoteItemsQuantity = $this->prepareQuoteProductsQuantity->execute($quote);
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            /** @var ProductInterface $inPostProduct */
            /** @var Item $quoteItem */
            $inPostProduct = $this->productFactory->create();
            $product = $this->quoteItemProductExtractor->extractProductFromQuoteItem($quoteItem);
            $websiteId = (int)$quote->getStore()->getWebsiteId();
            $qty = (float)$quoteItem->getQty();
            $options = [];

            if ($quoteItem->getProduct()->getTypeId() === Configurable::TYPE_CODE) {
                $option = $quoteItem->getOptionByCode('simple_product');
                if ($option instanceof Option) {
                    $quoteItem->getProduct()->setData(
                        ProductToInPostProductDataTransfer::CONFIGURABLE_CHILD_PRODUCT,
                        $option->getProduct()
                    );
                }
                // @phpstan-ignore-next-line
                $options = $quoteItem->getProduct()->getTypeInstance()->getSelectedAttributesInfo(
                    $quoteItem->getProduct()
                );
            } elseif ($quoteItem->getProduct()->getTypeId() === Type::TYPE_BUNDLE) {
                $children = $quoteItem->getChildren();
                $product->setData(ProductToInPostProductDataTransfer::BUNDLE_CHILD_PRODUCTS, $children);
                $selectedOptions = $quoteItem->getProduct()
                    ->getTypeInstance()->getOrderOptions($quoteItem->getProduct());
                if ($selectedOptions && $selectedOptions['bundle_options']) {
                    foreach ($selectedOptions['bundle_options'] as $option) {
                        $options[] = [
                            'label' => $option['label'],
                            'value' => (float) $option['value'][0]['qty'] . ' x ' . $option['value'][0]['title']
                                . ' ' . DecimalCalculator::round((float)$option['value'][0]['price'])
                                . ' ' . $quote->getStore()->getCurrentCurrency()->getCurrencySymbol()
                            ];
                    }
                }
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
            }

            $this->productToInPostProductDataTransfer->transfer(
                $product,
                $inPostProduct,
                $websiteId,
                $qty,
                $options,
                $quoteItemsQuantity
            );

            if ($this->omnibusProductLowestPriceProvider->canSendLowestPrice($quoteItem)) {
                $lowestPrice = $this->omnibusProductLowestPriceProvider->getLowestPrice($product);
                if ($lowestPrice) {
                    $inPostProduct->setLowestPrice($lowestPrice);
                }
            }

            if ($quoteItem->getProduct()->getTypeId() === Type::TYPE_BUNDLE) {
                $inPostProduct->setProductId($inPostProduct->getProductId() . '_' . $quoteItem->getId());
                $basePriceExclTax = DecimalCalculator::round((float)$quoteItem->getBasePrice());
                $basePriceInclTax = DecimalCalculator::round((float)$quoteItem->getBasePriceInclTax());
                $baseTaxValue = DecimalCalculator::sub($basePriceInclTax, $basePriceExclTax);
                /** @var PriceInterface $basePrice */
                $basePrice = $this->priceFactory->create();
                $basePrice->setNet($basePriceExclTax);
                $basePrice->setGross($basePriceInclTax);
                $basePrice->setVat($baseTaxValue);
                $inPostProduct->setBasePrice($basePrice);
            }

            $priceExclTax = DecimalCalculator::round((float)$quoteItem->getPrice());
            $priceInclTax = DecimalCalculator::round((float)$quoteItem->getPriceInclTax());
            $taxValue = DecimalCalculator::sub($priceInclTax, $priceExclTax);

            /** @var PriceInterface $promoPrice */
            $promoPrice = $this->priceFactory->create();
            $promoPrice->setNet($priceExclTax);
            $promoPrice->setGross($priceInclTax);
            $promoPrice->setVat($taxValue);
            $inPostProduct->setPromoPrice($promoPrice);
            $this->checkBasketStockAvailability((string)$basket->getBasketId(), $inPostProduct);
            $products[] = $inPostProduct;
        }

        $basket->setProducts($products);
    }

    private function isRestricted(int $productId, int $websiteId): bool
    {
        $restrictedProductIds = $this->restrictedProductIdsProvider->getList($websiteId);

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

    private function checkBasketStockAvailability(string $basketId, ProductInterface $product): void
    {
        $quantity = $product->getQuantity();
        $basketQuantity = (float)$quantity->getQuantity();
        $availableQuantity = $quantity->getAvailableQuantity();
        if ($basketQuantity > $availableQuantity) {
            if ($availableQuantity < 0) {
                $availableQuantity = 0;
            }

            $error = __(
                'Item "%1" is no longer available in requested quantity: %2. Currently available: %3',
                $product->getProductName(),
                $basketQuantity,
                $availableQuantity
            )->render();

            $this->logger->warning(
                sprintf('Basket %s Stock Validation Warning: %s', $basketId, $error)
            );

            $this->addBasketNotice($basketId, $error);
        }
    }
}
