<?php

declare(strict_types=1);

namespace InPost\Restrictions\Model\Cache\RestrictedProducts;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;

class Type extends TagScope
{
    public const TYPE_IDENTIFIER = 'inpost_restricted_products';
    public const CACHE_TAG = 'INPOST_RESTRICTED_PRODUCTS';
    public const TTL = 86400;

    public function __construct(
        FrontendPool $cacheFrontendPool
    ) {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}
