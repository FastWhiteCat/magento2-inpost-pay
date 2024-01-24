<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Order;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;
use InPost\InPostPay\Exception\QuoteItemOutOfStockException;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\IsSalableWithReservationsCondition;
use Magento\InventorySalesApi\Model\GetSalableQtyInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Psr\Log\LoggerInterface;

class StockValidator implements OrderValidatorInterface
{
    public function __construct(
        private readonly StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        private readonly IsSalableWithReservationsCondition $isSalableWithReservationsCondition,
        private readonly GetSalableQtyInterface $getSalableQty,
        private readonly LoggerInterface$logger
    ) {
    }

    /**
     * @param Quote $quote
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @param OrderInterface $inPostOrder
     * @return void
     * @throws QuoteItemOutOfStockException
     * @throws InputException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, OrderInterface $inPostOrder): void
    {
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteIdResolver->execute($websiteId)->getStockId();
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProductType() === Type::TYPE_BUNDLE) {
                foreach ($item->getChildren() as $child) {
                    $this->checkProductIsSalable($child, $stockId);
                }
            } else {
                $this->checkProductIsSalable($item, $stockId);
            }
        }
    }

    private function checkProductIsSalable(Item | AbstractItem $item, int $stockId): void
    {
        $name = (string)$item->getName();
        $sku = (string)$item->getSku();
        $qty = (float)$item->getQty();
        $stockValidationResult = $this->isSalableWithReservationsCondition->execute($sku, $stockId, $qty);
        $errors = $stockValidationResult->getErrors();

        foreach ($errors as $error) {
            $this->logger->error($error->getMessage());

            throw new QuoteItemOutOfStockException(
                __(
                    'Item "%1" is no longer available in requested quantity: %2. Currently available: %3',
                    $name,
                    $qty,
                    $this->getSalableQty->execute($sku, $stockId)
                )
            );
        }
    }
}
