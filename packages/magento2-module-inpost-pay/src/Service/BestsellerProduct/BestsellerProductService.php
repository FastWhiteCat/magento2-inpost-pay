<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\BestsellerProduct;

use Magento\Store\Model\App\Emulation as StoreEmulator;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Psr\Log\LoggerInterface;

class BestsellerProductService
{
    /**
     * @param StoreManagerInterface $storeManager
     * @param StoreEmulator $storeEmulator
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected readonly StoreManagerInterface $storeManager,
        protected readonly StoreEmulator $storeEmulator,
        protected readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return Store[]
     */
    protected function getDefaultStoresForWebsites(): array
    {
        $stores = [];

        foreach ($this->storeManager->getWebsites() as $website) {
            if (!$website instanceof Website) {
                continue;
            }

            $store = $website->getDefaultStore();

            if ($store->getId()) {
                $stores[] = $store;
            }
        }

        return $stores;
    }
}
