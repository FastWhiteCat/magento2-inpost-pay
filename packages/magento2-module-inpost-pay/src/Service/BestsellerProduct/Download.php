<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\BestsellerProduct;

use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Model\Source\Store\BestsellerProductPriority;
use InPost\InPostPay\Service\ApiConnector\GetBestsellers;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\App\Emulation as StoreEmulator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Psr\Log\LoggerInterface;

class Download
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
        private readonly StoreManagerInterface $storeManager,
        private readonly StoreEmulator $storeEmulator,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void
    {
        foreach ($this->storeManager->getWebsites() as $website) {
            if (!$website instanceof Website) {
                continue;
            }

            $store = $website->getDefaultStore();
            $websiteId = (int)$store->getWebsiteId();
            $this->storeEmulator->startEnvironmentEmulation((int)$store->getId(), Area::AREA_FRONTEND, true);
            $inPostBestsellers = $this->getBestsellers->execute();
            $this->cleanBestsellersByWebsiteId($websiteId);
            $priority = BestsellerProductPriority::MIN_PRIORITY;

            foreach ($inPostBestsellers as $inPostBestseller) {
                $this->createBestsellerProductForWebsiteId($websiteId, $inPostBestseller, $priority);
                $priority++;

                if ($priority > BestsellerProductPriority::MAX_PRIORITY) {
                    break;
                }
            }
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
     * @param int $priority
     * @return void
     * @throws LocalizedException
     */
    private function createBestsellerProductForWebsiteId(
        int $websiteId,
        BestsellerProductInterface $inPostBestseller,
        int $priority
    ): void {
        $magentoBestsellerProduct = $this->creator->createMagentoBestsellerProduct(
            $websiteId,
            $inPostBestseller,
            $priority
        );

        $this->logger->debug(
            sprintf(
                'New Bestseller Product [SKU:%s] has been downloaded from InPost Pay',
                $magentoBestsellerProduct->getSku()
            )
        );
    }
}
