<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\BestsellerProduct;

use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterfaceFactory;
use InPost\InPostPay\Exception\NotFullySuccessfulBestsellerProductUploadException;
use InPost\InPostPay\Service\ApiConnector\DeleteBestseller;
use InPost\InPostPay\Service\ApiConnector\PostBestsellers;
use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Service\DataTransfer\BestsellerProductDataTransfer;
use InPost\InPostPay\Model\ResourceModel\InPostPayBestsellerProduct\CollectionFactory as BestsellersCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\App\Emulation as StoreEmulator;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Upload extends BestsellerProductService
{
    /**
     * @param BestsellersCollectionFactory $bestsellersCollectionFactory
     * @param BestsellerProductInterfaceFactory $bestsellerProductFactory
     * @param BestsellerProductDataTransfer $bestsellerProductDataTransfer
     * @param PostBestsellers $postBestsellers
     * @param UploadResponseHandler $uploadResponseHandler
     * @param DeleteBestseller $deleteBestseller
     * @param StoreEmulator $storeEmulator
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly BestsellersCollectionFactory $bestsellersCollectionFactory,
        private readonly BestsellerProductInterfaceFactory $bestsellerProductFactory,
        private readonly BestsellerProductDataTransfer $bestsellerProductDataTransfer,
        private readonly PostBestsellers $postBestsellers,
        private readonly UploadResponseHandler $uploadResponseHandler,
        private readonly DeleteBestseller $deleteBestseller,
        StoreEmulator $storeEmulator,
        StoreManagerInterface $storeManager,
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
        $fullSuccess = true;

        foreach ($this->getDefaultStoresForWebsites() as $store) {
            $websiteId = (int)$store->getWebsiteId();
            $this->storeEmulator->startEnvironmentEmulation((int)$store->getId(), Area::AREA_FRONTEND, true);
            $bestsellerProducts = [];

            try {
                foreach ($this->getBestsellersByWebsiteId($websiteId) as $magentoBestsellerProduct) {
                    $bestsellerProduct = $this->bestsellerProductFactory->create();
                    $this->bestsellerProductDataTransfer->transfer(
                        $magentoBestsellerProduct,
                        $bestsellerProduct
                    );

                    $bestsellerProducts[] = $bestsellerProduct;
                    $this->deleteBestseller->deleteBestsellerByProductId(
                        (int)$bestsellerProduct->getProductId(),
                        true
                    );
                }

                $response = $this->postBestsellers->execute($bestsellerProducts);
                $fullSuccess = $this->uploadResponseHandler->handleResponse($response, $websiteId);

                if ($fullSuccess) {
                    $this->logger->debug('InPost Bestseller Product successfully uploaded.');
                } else {
                    $this->logger->warning('InPost Bestseller Product were uploaded with errors.');
                    $fullSuccess = false;
                }
            } catch (LocalizedException $e) {
                $this->storeEmulator->stopEnvironmentEmulation();
                $this->logger->error(sprintf('Could not upload bestseller products. Reason: %s', $e->getMessage()));

                throw $e;
            }

            $this->storeEmulator->stopEnvironmentEmulation();
        }

        if (!$fullSuccess) {
            throw new NotFullySuccessfulBestsellerProductUploadException(
                __('Uploading Bestsellers to InPost Pay was completed but some products have errors.')
            );
        }
    }

    /**
     * @param int $websiteId
     * @return InPostPayBestsellerProductInterface[]
     */
    private function getBestsellersByWebsiteId(int $websiteId): array
    {
        $collection = $this->bestsellersCollectionFactory->create();
        $collection->addFieldToFilter(InPostPayBestsellerProductInterface::WEBSITE_ID, ['eq' => $websiteId]);
        $collection->addOrder(InPostPayBestsellerProductInterface::PRIORITY, 'ASC');
        $bestsellers = [];

        foreach ($collection->getItems() as $item) {
            if ($item instanceof InPostPayBestsellerProductInterface) {
                $bestsellers[] = $item;
            }
        }

        return $bestsellers;
    }
}
