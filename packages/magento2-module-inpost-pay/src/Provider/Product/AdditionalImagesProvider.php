<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Collection;

class AdditionalImagesProvider
{
    /**
     * @param Product $product
     * @return Collection
     */
    public function execute(Product $product): Collection
    {
        return $product->getMediaGalleryImages();
    }
}
