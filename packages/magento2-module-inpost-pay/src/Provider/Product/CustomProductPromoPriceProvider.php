<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Product;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomProductPromoPriceProvider extends OmnibusProductLowestPriceProvider
{
    /**
     * @param Product $product
     * @param string $customPromoPriceAttributeCode
     * @return PriceInterface|null
     * @throws NoSuchEntityException
     */
    public function getCustomPromoPrice(Product $product, string $customPromoPriceAttributeCode): ?PriceInterface
    {
        $customPromoPriceAttribute = $product->getCustomAttribute($customPromoPriceAttributeCode);
        $customPromoPriceValue = $customPromoPriceAttribute?->getValue() ?? null;

        if (!is_scalar($customPromoPriceValue)) {
            return null;
        }

        $priceIncludesTax = $this->taxConfig->priceIncludesTax($product->getStore());
        $taxRate = $this->getProductTaxRate($product);
        $taxRateMultiplier = ($taxRate / 100) + 1;

        if ($priceIncludesTax) {
            $gross = (float)$customPromoPriceValue;
            $net = $gross / $taxRateMultiplier;
        } else {
            $net = (float)$customPromoPriceValue;
            $gross = $net * $taxRateMultiplier;
        }

        /** @var PriceInterface $customPromoPrice */
        $customPromoPrice = $this->priceFactory->create();
        $customPromoPrice->setGross(round($gross, 2));
        $customPromoPrice->setNet(round($net, 2));
        $customPromoPrice->setVat(round($gross - $net, 2));

        return $customPromoPrice;
    }
}
