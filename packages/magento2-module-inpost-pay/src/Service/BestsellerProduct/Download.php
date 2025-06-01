<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\BestsellerProduct;

use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Service\ApiConnector\GetBestsellers;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\App\Emulation as StoreEmulator;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Download extends BestsellerProductService
{
    /**
     * @param Cleaner $cleaner
     * @param Creator $creator
     * @param GetBestsellers $getBestsellers
     * @param StoreManagerInterface $storeManager
     * @param StoreEmulator $storeEmulator
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Cleaner $cleaner,
        private readonly Creator $creator,
        private readonly GetBestsellers $getBestsellers,
        StoreManagerInterface $storeManager,
        StoreEmulator $storeEmulator,
        LoggerInterface $logger
    ) {
        parent::__construct($storeManager, $storeEmulator, $logger);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void
    {
        foreach ($this->getDefaultStoresForWebsites() as $store) {
            $websiteId = (int)$store->getWebsiteId();
            $storeId = (int)$store->getId();
            $this->storeEmulator->startEnvironmentEmulation((int)$store->getId(), Area::AREA_FRONTEND, true);

            try {
                $inPostBestsellers = $this->getBestsellers->execute($storeId);
                $this->cleanBestsellersByWebsiteId($websiteId);

                foreach ($inPostBestsellers as $inPostBestseller) {
                    $this->createBestsellerProductForWebsiteId($websiteId, $inPostBestseller);
                }
            } catch (LocalizedException $e) {
                $this->storeEmulator->stopEnvironmentEmulation();
                $this->logger->error(sprintf('Could not download bestseller products. Reason: %s', $e->getMessage()));

                throw $e;
            }

            $this->storeEmulator->stopEnvironmentEmulation();
        }
    }

    /**
     * @param int $websiteId
     * @return void
     * @throws LocalizedException
     */
    private function cleanBestsellersByWebsiteId(int $websiteId): void
    {
        $this->cleaner->deleteAllMagentoBestsellerProducts($websiteId);
        $this->logger->debug(
            sprintf('Magento bestseller product from website ID:%s have been removed.', $websiteId)
        );
    }

    /**
     * @param int $websiteId
     * @param BestsellerProductInterface $inPostBestseller
     * @return void
     * @throws LocalizedException
     */
    private function createBestsellerProductForWebsiteId(
        int $websiteId,
        BestsellerProductInterface $inPostBestseller
    ): void {
        $magentoBestsellerProduct = $this->creator->createMagentoBestsellerProduct(
            $websiteId,
            $inPostBestseller
        );

        $this->logger->debug(
            sprintf(
                'New Bestseller Product [SKU:%s] has been downloaded from InPost Pay',
                $magentoBestsellerProduct->getSku()
            )
        );
    }
}
