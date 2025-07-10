<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Bestseller;

use InPost\InPostPay\Exception\InvalidBestsellerProductDataException;
use InPost\InPostPay\Provider\Product\BestsellerProductEanProvider;
use Magento\Catalog\Model\Product;

class EanValidator
{
    /**
     * @param BestsellerProductEanProvider $bestsellerProductEanProvider
     */
    public function __construct(
        private readonly BestsellerProductEanProvider $bestsellerProductEanProvider
    ) {
    }

    /**
     * @param Product $product
     * @return void
     * @throws InvalidBestsellerProductDataException
     */
    public function validate(Product $product): void
    {
        $this->bestsellerProductEanProvider->get($product);
    }
}
