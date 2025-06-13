<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\BestsellerProduct;

use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterfaceFactory;
use InPost\InPostPay\Exception\BestsellerProductsLimitReachedException;
use InPost\InPostPay\Exception\CouldNotDeleteInPostPayBestsellerProductException;
use InPost\InPostPay\Exception\NotFullySuccessfulBestsellerProductUploadException;
use InPost\InPostPay\Provider\Config\BestsellersCronConfigProvider;
use InPost\InPostPay\Service\ApiConnector\DeleteBestseller;
use InPost\InPostPay\Service\ApiConnector\GetBestsellers;
use InPost\InPostPay\Service\ApiConnector\PostBestsellers;
use InPost\InPostPay\Service\ApiConnector\PutBestseller;
use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Service\DataTransfer\BestsellerProductDataTransfer;
use InPost\InPostPay\Model\ResourceModel\InPostPayBestsellerProduct\CollectionFactory as BestsellersCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\App\Emulation as StoreEmulator;
use Magento\Store\Model\Store;
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
     * @param PutBestseller $putBestseller
     * @param UploadResponseHandler $uploadResponseHandler
     * @param DeleteBestseller $deleteBestseller
     * @param GetBestsellers $getBestsellers
     * @param BestsellersCronConfigProvider $bestsellersCronConfigProvider
     * @param StoreEmulator $storeEmulator
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly BestsellersCollectionFactory $bestsellersCollectionFactory,
        private readonly BestsellerProductInterfaceFactory $bestsellerProductFactory,
        private readonly BestsellerProductDataTransfer $bestsellerProductDataTransfer,
        private readonly PostBestsellers $postBestsellers,
        private readonly PutBestseller $putBestseller,
        private readonly UploadResponseHandler $uploadResponseHandler,
        private readonly DeleteBestseller $deleteBestseller,
        private readonly GetBestsellers $getBestsellers,
        private readonly BestsellersCronConfigProvider $bestsellersCronConfigProvider,
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
            $storeId = (int)$store->getId();

            if (!$this->bestsellersCronConfigProvider->isSynchronizationEnabled($storeId)) {
                continue;
            }

            $this->storeEmulator->startEnvironmentEmulation((int)$store->getId(), Area::AREA_FRONTEND, true);
            $bestsellerProducts = [];
            $inPostPayBestsellerProductIds = $this->getExistingInPostBestsellerProductIds($storeId);
            $this->storeEmulator->stopEnvironmentEmulation();

            try {
                foreach ($this->getBestsellersByWebsiteId($websiteId) as $magentoBestsellerProduct) {
                    /** @var BestsellerProductInterface $bestsellerProduct */
                    $bestsellerProduct = $this->bestsellerProductFactory->create();
                    $this->bestsellerProductDataTransfer->transfer(
                        $magentoBestsellerProduct,
                        $bestsellerProduct
                    );

                    $bestsellerProducts[(int)$bestsellerProduct->getProductId()] = $bestsellerProduct;
                }

                $productIdsToCreateInInPostPay = array_diff(
                    array_keys($bestsellerProducts),
                    $inPostPayBestsellerProductIds
                );

                $productIdsToDeleteFromInPostPay = array_diff(
                    $inPostPayBestsellerProductIds,
                    array_keys($bestsellerProducts)
                );

                $productIdsToUpdateInInPostPay = array_intersect(
                    array_keys($bestsellerProducts),
                    $inPostPayBestsellerProductIds
                );

                $bestsellerProductsToCreate = array_intersect_key(
                    $bestsellerProducts,
                    array_flip($productIdsToCreateInInPostPay)
                );

                $bestsellerProductsToUpdate = array_intersect_key(
                    $bestsellerProducts,
                    array_flip($productIdsToUpdateInInPostPay)
                );

                $this->storeEmulator->startEnvironmentEmulation((int)$store->getId(), Area::AREA_FRONTEND, true);
                $this->deleteProductIdsFromInPostPay($productIdsToDeleteFromInPostPay, $storeId);
                $createSuccess = $this->postInPostBestsellerProducts($bestsellerProductsToCreate, $store);
                $updateSuccess = $this->putInPostBestsellerProducts($bestsellerProductsToUpdate, $store);
                $this->storeEmulator->stopEnvironmentEmulation();

                if ($createSuccess && $updateSuccess) {
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
    public function getBestsellersByWebsiteId(int $websiteId): array
    {
        $collection = $this->bestsellersCollectionFactory->create();
        $collection->addFieldToFilter(InPostPayBestsellerProductInterface::WEBSITE_ID, ['eq' => $websiteId]);
        $bestsellers = [];

        foreach ($collection->getItems() as $item) {
            if ($item instanceof InPostPayBestsellerProductInterface) {
                $bestsellers[] = $item;
            }
        }

        return $bestsellers;
    }

    /**
     * @return int[]
     */
    public function getExistingInPostBestsellerProductIds(int $storeId): array
    {
        $inPostPayBestsellerProductIds = [];

        try {
            $inPostPayBestsellerProducts = $this->getBestsellers->execute($storeId);
        } catch (LocalizedException $e) {
            $inPostPayBestsellerProducts = [];
        }

        foreach ($inPostPayBestsellerProducts as $inPostPayBestsellerProduct) {
            $inPostPayBestsellerProductIds[] = (int)$inPostPayBestsellerProduct->getProductId();
        }

        return $inPostPayBestsellerProductIds;
    }

    /**
     * @param array $productIdsToDeleteFromInPostPay
     * @param int $storeId
     * @return void
     */
    public function deleteProductIdsFromInPostPay(array $productIdsToDeleteFromInPostPay, int $storeId): void
    {
        foreach ($productIdsToDeleteFromInPostPay as $productIdToDeleteFromInPostPay) {
            try {
                $this->deleteBestseller->deleteBestsellerByProductId(
                    $productIdToDeleteFromInPostPay,
                    $storeId,
                    true
                );
            } catch (CouldNotDeleteInPostPayBestsellerProductException $e) {
                $this->logger->error(
                    'Could not delete InPost Bestseller Product from InPost Pay. Reason: ' . $e->getMessage()
                );

                continue;
            }
        }
    }

    /**
     * @param array $bestsellerProducts
     * @param Store $store
     * @return bool
     * @throws LocalizedException
     */
    public function postInPostBestsellerProducts(array $bestsellerProducts, Store $store): bool
    {
        $storeId = (int)$store->getId();
        $websiteId = (int)$store->getWebsiteId();
        $response = $this->postBestsellers->execute($bestsellerProducts, $storeId);
        $success = $this->uploadResponseHandler->handlePostResponse($response, $websiteId);

        if ($success) {
            $this->logger->debug('InPost Bestseller Product successfully created.');
        } else {
            $this->logger->warning('InPost Bestseller Product were created with errors.');
        }

        return $success;
    }

    /**
     * @param array $bestsellerProducts
     * @param Store $store
     * @return bool
     * @throws LocalizedException
     */
    public function putInPostBestsellerProducts(array $bestsellerProducts, Store $store): bool
    {
        $fullSuccess = true;

        if (empty($bestsellerProducts)) {
            return true;
        }

        foreach ($bestsellerProducts as $bestsellerProduct) {
            $storeId = (int)$store->getId();
            $websiteId = (int)$store->getWebsiteId();
            $response = $this->putBestseller->execute($bestsellerProduct, $storeId);
            $success = $this->uploadResponseHandler->handlePutResponse($response, $websiteId);

            if (!$success) {
                $fullSuccess = false;
            }
        }

        if ($fullSuccess) {
            $this->logger->debug('InPost Bestseller Product successfully updated.');
        } else {
            $this->logger->warning('InPost Bestseller Product were updated with errors.');
        }

        return $fullSuccess;
    }
}
