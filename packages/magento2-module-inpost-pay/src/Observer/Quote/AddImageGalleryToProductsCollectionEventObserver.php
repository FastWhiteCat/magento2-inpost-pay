<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Quote;

use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class AddImageGalleryToProductsCollectionEventObserver implements ObserverInterface
{
    /**
     * @param GeneralConfigProvider $generalConfigProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly GeneralConfigProvider $generalConfigProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(Observer $observer): void
    {
        try {
            /** @var ProductCollection $productCollection */
            $productCollection = $observer->getData('collection');
            $storeId = (int)$productCollection->getStoreId();

            if (!$this->generalConfigProvider->isAdditionalImagesEnabled($storeId)) {
                return;
            }

            $productCollection->addMediaGalleryData();
        } catch (LocalizedException $e) {
            $this->logger->error(
                sprintf(
                    'Could not append product collection with media gallery. Reason: %s',
                    $e->getMessage()
                )
            );
        }
    }
}
