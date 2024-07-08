<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator;

use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Enum\InPostDeliveryType;
use InPost\InPostPay\Exception\InPostPayRestrictedProductException;
use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Provider\RestrictedProductIdsProvider;
use Magento\Quote\Model\Quote;

class OrderRestrictionsValidator
{
    private $deliveryTypes = [];

    public function __construct(
        private readonly RestrictedProductIdsProvider $restrictedProductIdsProvider,
    ) {
        $this->deliveryTypes = [
            InPostDeliveryType::COURIER->name => RestrictionsRuleInterface::APPLIES_TO_COURIER,
            InPostDeliveryType::APM->name => RestrictionsRuleInterface::APPLIES_TO_APM
        ];
    }

    /**
     * @throws InPostPayRestrictedProductException
     */
    public function validate(Quote $quote, OrderInterface $inPostOrder, bool $everyOccurenceMode = false): void
    {
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        $restrictedProduct = null;
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $productId = (int)$product->getId();
            $appliesTo =  $this->deliveryTypes[$inPostOrder->getDelivery()->getDeliveryType()];
            if ($this->isProductRestricted($productId, $websiteId, $appliesTo)) {
                if ($everyOccurenceMode) {
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

    private function isProductRestricted(int $productId, int $websiteId, int $appliesTo): bool
    {
        return in_array(
            $productId,
            $this->restrictedProductIdsProvider->getList($websiteId)
        ) || in_array(
            $productId,
            $this->restrictedProductIdsProvider->getList($websiteId, $appliesTo)
        );
    }
}
