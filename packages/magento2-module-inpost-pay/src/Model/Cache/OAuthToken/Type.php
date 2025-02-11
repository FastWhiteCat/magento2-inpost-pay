<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Cache\OAuthToken;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;

class Type extends TagScope
{
    public const TYPE_IDENTIFIER = 'inpost_pay_oauth_token';
    public const CACHE_TAG = 'INPOST_PAY_OAUTH_TOKEN';
    public const TTL = 240;

    public function __construct(
        FrontendPool $cacheFrontendPool
    ) {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}
