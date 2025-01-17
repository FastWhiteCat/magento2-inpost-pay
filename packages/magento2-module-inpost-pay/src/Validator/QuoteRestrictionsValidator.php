<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator;

use InPost\InPostPay\Exception\InPostPayRestrictedProductException;
use InPost\Restrictions\Provider\RestrictedProductIdsProvider;
use Magento\Quote\Model\Quote;

class QuoteRestrictionsValidator
{
    public function __construct(
        private readonly RestrictedProductIdsProvider $restrictedProductIdsProvider,
    ) {
    }

    /**
     * @throws InPostPayRestrictedProductException
     */
    public function validate(Quote $quote, bool $everyOccurrenceMode = false): void
    {
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        $restrictedProduct = null;
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $productId = (int)$product->getId();
            if ($this->isProductRestricted($productId, $websiteId)) {
                if ($everyOccurrenceMode) {
                    $this->createExceptionForRestrictedProduct((string)$product->getName());
                } else {
                    $restrictedProduct = $product;
                }
            }
        }

        if ((int)$quote->getItemsCount() === 1 && $restrictedProduct !== null) {
            $this->createExceptionForRestrictedProduct((string)$restrictedProduct->getName());
        }
    }

    private function createExceptionForRestrictedProduct(string $productName): string
    {
        $errorPhrase = __(
            'Product "%1" is not available for InPost Pay.',
            mb_substr($productName, 0, 50)
        );

        throw new InPostPayRestrictedProductException($errorPhrase);
    }

    private function isProductRestricted(int $productId, int $websiteId): bool
    {
        return in_array(
            $productId,
            $this->restrictedProductIdsProvider->getList($websiteId)
        );
    }
}
