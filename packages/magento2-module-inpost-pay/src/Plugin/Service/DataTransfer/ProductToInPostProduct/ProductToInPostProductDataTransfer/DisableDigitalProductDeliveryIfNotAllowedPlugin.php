<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;

use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use InPost\InPostPay\Registry\Quote\DigitalQuoteAllowRegistry;
use InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct\ProductToInPostProductDataTransfer;
use Magento\Catalog\Model\Product;

class DisableDigitalProductDeliveryIfNotAllowedPlugin
{
    public function __construct(
        private readonly DigitalQuoteAllowRegistry $digitalQuoteAllowRegistry
    ) {
    }

    /**
     * @param ProductToInPostProductDataTransfer $subject
     * @param $result
     * @param Product $product
     * @param ProductInterface $inPostProduct
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterTransfer( //@phpstan-ignore-line
        ProductToInPostProductDataTransfer $subject,
        $result,
        Product $product,
        ProductInterface $inPostProduct
    ) {
        $isAllowed = $this->digitalQuoteAllowRegistry->isCurrentlyProcessedDigitalQuoteAllowed();

        if (!$isAllowed) {
            foreach ($inPostProduct->getDeliveryProduct() as $deliveryProduct) {
                if (!$product->isVirtual()) {
                    continue;
                }

                $deliveryProduct->setIfDeliveryAvailable(false);
            }
        }
    }
}
