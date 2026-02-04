<?php
declare(strict_types=1);

namespace InPost\InPostPay\Service;

use InPost\InPostPay\Service\Cart\Item\QuoteItemProductExtractor;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote;

class PrepareQuoteProductsQuantity
{
    public function __construct(
        private readonly QuoteItemProductExtractor $quoteItemProductExtractor
    ) {
    }

    public function execute(Quote $quote): array
    {
        $quoteItemsQuantity = [];
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            if ($quoteItem->getProduct()->getTypeId() === Type::TYPE_BUNDLE) {
                foreach ($quoteItem->getChildren() as $child) {
                    $qty = (float)$child->getQty() * (float)$quoteItem->getQty();
                    $this->setQuoteItemQuantity((int)$child->getProduct()->getId(), $qty, $quoteItemsQuantity);
                }
            } elseif ($quoteItem->getProduct()->getTypeId() === Configurable::TYPE_CODE) {
                $qty = (float)$quoteItem->getQty();
                $product = $this->quoteItemProductExtractor->extractProductFromQuoteItem($quoteItem);
                $this->setQuoteItemQuantity((int)$product->getId(), $qty, $quoteItemsQuantity);
            } else {
                $qty = (float)$quoteItem->getQty();
                $this->setQuoteItemQuantity((int)$quoteItem->getProduct()->getId(), $qty, $quoteItemsQuantity);
            }
        }

        return $quoteItemsQuantity;
    }

    private function setQuoteItemQuantity(int $productId, float $qty, array &$quoteItemsQuantity): void
    {
        if (array_key_exists($productId, $quoteItemsQuantity)) {
            $quoteItemsQuantity[$productId] += $qty;
        } else {
            $quoteItemsQuantity[$productId] = $qty;
        }
    }
}
