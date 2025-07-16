<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Product;

use InPost\InPostPay\Exception\InvalidBestsellerProductDataException;
use InPost\InPostPay\Provider\Config\EanConfigProvider;
use Magento\Catalog\Model\Product;

class BestsellerProductEanProvider
{
    /**
     * @param EanConfigProvider $eanConfigProvider
     */
    public function __construct(
        private readonly EanConfigProvider $eanConfigProvider
    ) {
    }

    /**
     * @param Product $product
     * @return string
     * @throws InvalidBestsellerProductDataException
     */
    public function get(Product $product): string
    {
        $eanAttributeCode = $this->eanConfigProvider->getProductEanAttributeCode();

        if ($eanAttributeCode === null) {
            throw new InvalidBestsellerProductDataException(
                __('EAN product attribute is not configured!')
            );
        }

        $ean = $product->getData($eanAttributeCode);

        if (is_scalar($ean)) {
            return (string)$ean;
        }

        $eanAttribute = $product->getCustomAttribute($eanAttributeCode);
        $ean = $eanAttribute?->getValue() ?? null;

        if (!is_scalar($ean)) {
            throw new InvalidBestsellerProductDataException(
                __(
                    'Product [SKU:%1] has empty EAN attribute and cannot be used as InPost Pay Bestseller.',
                    $product->getSku()
                )
            );
        }

        return (string)$ean;
    }
}
