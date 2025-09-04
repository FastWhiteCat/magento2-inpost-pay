<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\CheckoutAgreement;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use InPost\InPostPay\Model\Cache\TermsAndConditions\Type as TermsAndConditionsCacheType;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Store\Model\StoreManagerInterface;

class ClearCacheAfterInPostPayCheckoutAgreementSaveEventObserver implements ObserverInterface
{
    /**
     * @param StoreManagerInterface $storeManager
     * @param CacheInterface $cache
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly CacheInterface $cache
    ) {
    }

    public function execute(Observer $observer): void
    {
        $agreement = $observer->getEvent()->getData(InPostPayCheckoutAgreementInterface::ENTITY_NAME);

        if ($agreement instanceof InPostPayCheckoutAgreementInterface) {
            $cacheIdentifiers = [TermsAndConditionsCacheType::TYPE_IDENTIFIER];

            foreach ($this->storeManager->getStores(true) as $store) {
                $cacheIdentifiers[] = sprintf(
                    '%s_%s',
                    TermsAndConditionsCacheType::TYPE_IDENTIFIER,
                    (int)$store->getId()
                );
            }

            $this->cache->clean($cacheIdentifiers);
        }
    }
}
