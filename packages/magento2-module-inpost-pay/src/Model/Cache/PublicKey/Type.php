<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Cache\PublicKey;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;

class Type extends TagScope
{
    public const TYPE_IDENTIFIER = 'inpost_pay_public_key';
    public const CACHE_TAG = 'INPOST_PAY_PUBLIC_KEY';
    public const TTL = 86400;

    public function __construct(
        FrontendPool $cacheFrontendPool
    ) {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}
