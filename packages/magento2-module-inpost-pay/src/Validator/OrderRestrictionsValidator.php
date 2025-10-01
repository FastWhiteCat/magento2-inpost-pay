<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator;

use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Enum\InPostDeliveryType;
use InPost\InPostPay\Exception\InPostPayRestrictedProductException;
use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Provider\RestrictedProductIdsProvider;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;

class OrderRestrictionsValidator
{
    private array $deliveryTypes;

    public function __construct(
        private readonly RestrictedProductIdsProvider $restrictedProductIdsProvider,
    ) {
        $this->deliveryTypes = [
            InPostDeliveryType::COURIER->name => RestrictionsRuleInterface::APPLIES_TO_COURIER,
            InPostDeliveryType::APM->name => RestrictionsRuleInterface::APPLIES_TO_APM,
            InPostDeliveryType::DIGITAL->name => RestrictionsRuleInterface::APPLIES_TO_DIGITAL
        ];
    }

    /**
     * @throws InPostPayRestrictedProductException
     */
    public function validate(Quote $quote, OrderInterface $inPostOrder, bool $everyOccurrenceMode = false): void
    {
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        $restrictedProduct = null;
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $appliesTo =  $this->deliveryTypes[$inPostOrder->getDelivery()->getDeliveryType()];

            if ($appliesTo === InPostDeliveryType::DIGITAL->name) {
                $appliesTo = RestrictionsRuleInterface::APPLIES_TO_BOTH;
            }

            if ($this->isProductRestricted($item, $websiteId, $appliesTo)) {
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

    private function isProductRestricted(Item $item, int $websiteId, int $appliesTo): bool
    {
        $product = $item->getProduct();
        $productId = (int)$product->getId();
        $simpleProductId = $productId;
        if ($item->getProduct()->getTypeId() === Configurable::TYPE_CODE) {
            $option = $item->getOptionByCode('simple_product');
            if ($option instanceof Option) {
                $simpleProductId = (int)$option->getProduct()->getId();
            }
        }

        $websiteRestrictedProductIds = $this->restrictedProductIdsProvider->getList($websiteId);
        $websiteRestrictedProductIdsAppliesTo = $this->restrictedProductIdsProvider->getList($websiteId, $appliesTo);

        return in_array($productId, $websiteRestrictedProductIds)
            || in_array($productId, $websiteRestrictedProductIdsAppliesTo)
            || in_array($simpleProductId, $websiteRestrictedProductIds)
            || in_array($simpleProductId, $websiteRestrictedProductIdsAppliesTo);
    }
}
