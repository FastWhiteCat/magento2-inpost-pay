<?php
declare(strict_types=1);

namespace InPost\InPostPay\Service;

use Magento\Catalog\Model\Product\Type;
use Magento\Quote\Model\Quote;

class PrepareQuoteProductsQuantity
{
    public function execute(Quote $quote): array
    {
        $quoteItemsQuantity = [];
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            if ($quoteItem->getProduct()->getTypeId() === Type::TYPE_BUNDLE) {
                foreach ($quoteItem->getChildren() as $child) {
                    $qty = $child->getQty() * $quoteItem->getQty();
                    $this->setQuoteItemQuantity((int)$child->getProduct()->getId(), $qty, $quoteItemsQuantity);
                }
            } else {
                $qty = $quoteItem->getQty();
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
