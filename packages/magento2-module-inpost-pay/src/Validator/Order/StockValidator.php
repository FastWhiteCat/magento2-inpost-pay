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
use Magento\InventorySales\Model\IsProductSalableCondition\ManageStockCondition;
use Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\IsSalableWithReservationsCondition;
use Magento\InventorySalesApi\Model\GetSalableQtyInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use InPost\InPostPay\Service\Product\ProductBackorderService;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockValidator implements OrderValidatorInterface
{
    public function __construct(
        private readonly StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        private readonly IsSalableWithReservationsCondition $isSalableWithReservationsCondition,
        private readonly GetSalableQtyInterface $getSalableQty,
        private readonly ManageStockCondition $manageStockCondition,
        private readonly LoggerInterface $logger,
        private readonly ProductBackorderService $productBackorderService
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
        $product = $item->getProduct();
        $unmanagedStock = $this->manageStockCondition->execute($sku, $stockId);
        $isBackOrdered = $this->productBackorderService->isProductBackOrdered($product, $stockId);

        if ($unmanagedStock || $isBackOrdered) {
            return;
        }

        $stockValidationResult = $this->isSalableWithReservationsCondition->execute($sku, $stockId, $qty);
        $errors = $stockValidationResult->getErrors();

        foreach ($errors as $error) {
            $this->logger->error($error->getMessage());
            $availableQty = $this->getSalableQty->execute($sku, $stockId);
            if ($availableQty < 0) {
                $availableQty = 0;
            }

            throw new QuoteItemOutOfStockException(
                __(
                    'Item "%1" is no longer available in requested quantity: %2. Currently available: %3',
                    $name,
                    $qty,
                    $availableQty
                )
            );
        }
    }
}
