<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\SalesRule;

use InPost\InPostPay\Model\Cache\Promotions\Type as PromotionsCacheType;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SalesRuleSaveEventObserver implements ObserverInterface
{
    public function __construct(private readonly CacheInterface $cache)
    {
    }

    /**
     * @param Observer $observer
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer): void
    {
        $this->cache->clean([PromotionsCacheType::TYPE_IDENTIFIER]);
    }
}
