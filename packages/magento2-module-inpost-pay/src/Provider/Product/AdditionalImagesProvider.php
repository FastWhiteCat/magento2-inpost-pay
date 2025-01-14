<?php
/**
 * Copyright © Fast White Cat S.A. All rights reserved.
 * See LICENSE_FASTWHITECAT for license details.
 */

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Product;

use Magento\Catalog\Model\Product;

class AdditionalImagesProvider
{
    /**
     * @param Product $product
     * @return \Magento\Framework\Data\Collection
     */
    public function execute(Product $product): \Magento\Framework\Data\Collection
    {
        return $product->getMediaGalleryImages();
    }
}
