<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\BestsellerProduct;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Api\InPostPayBestsellerProductRepositoryInterface;
use InPost\InPostPay\Enum\InPostBestsellerProductStatus;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime;
use Psr\Log\LoggerInterface;

class UploadResponseHandler
{
    public const CONTENT_KEY = 'content';
    public const ERROR_KEY = 'error';
    public const ERROR_REASON_KEY = 'reason';

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param InPostPayBestsellerProductRepositoryInterface $inPostPayBestsellerProductRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly InPostPayBestsellerProductRepositoryInterface $inPostPayBestsellerProductRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param array $response
     * @param int $websiteId
     * @return bool True on full success
     */
    public function handlePostResponse(array $response, int $websiteId): bool
    {
        $successfulData = (array)($response[self::CONTENT_KEY] ?? []);
        $errorData = (array)($response[self::ERROR_KEY] ?? []);

        $success = $this->handleSuccessfulData($successfulData, $websiteId);
        $this->handleErrorData($errorData, $websiteId);

        return empty($errorData) && $success;
    }

    /**
     * @param array $response
     * @param int $websiteId
     * @return bool True on full success
     */
    public function handlePutResponse(array $response, int $websiteId): bool
    {
        return $this->handleSuccessfulData([$response], $websiteId);
    }

    /**
     * @param array $successfulData
     * @param int $websiteId
     * @return bool
     */
    private function handleSuccessfulData(array $successfulData, int $websiteId): bool
    {
        $success = true;

        foreach ($successfulData as $successfulProductData) {
            $productId = (int)($successfulProductData[BestsellerProductInterface::PRODUCT_ID] ?? 0);
            $qrCode = (string)($successfulProductData[BestsellerProductInterface::QR_CODE] ?? '');
            $deepLink = (string)($successfulProductData[BestsellerProductInterface::DEEP_LINK] ?? '');
            $status = (string)($successfulProductData[BestsellerProductInterface::STATUS] ?? '');
            $sku = $this->getProductSkuById($productId);

            if ($sku === null) {
                continue;
            }

            $inPostPayBestsellerProduct = $this->getInPostPayBestsellerProduct($sku, $websiteId);

            if ($inPostPayBestsellerProduct === null) {
                continue;
            }

            $status = !empty($status) ? $status : InPostBestsellerProductStatus::INACTIVE->value;
            $inPostPayBestsellerProduct->setQrCode($qrCode);
            $inPostPayBestsellerProduct->setDeepLink($deepLink);
            $inPostPayBestsellerProduct->setInPostPayStatus($status);
            $inPostPayBestsellerProduct->setSynchronizedAt(date(DateTime::DATETIME_PHP_FORMAT));

            try {
                $this->inPostPayBestsellerProductRepository->save($inPostPayBestsellerProduct);
            } catch (CouldNotSaveException $e) {
                $this->logger->error(
                    sprintf(
                        'Could not update Bestseller Product [SKU:%s] for website ID: %s with QR Code. Reason: %s',
                        $sku,
                        $websiteId,
                        $e->getMessage()
                    )
                );

                $success = false;
            }
        }

        return $success;
    }

    /**
     * @param array $errorData
     * @param int $websiteId
     * @return void
     */
    private function handleErrorData(array $errorData, int $websiteId): void
    {
        foreach ($errorData as $errorProductData) {
            $productId = (int)($errorProductData[BestsellerProductInterface::PRODUCT_ID] ?? 0);
            $error = (string)($errorProductData[self::ERROR_REASON_KEY] ?? '');
            $sku = $this->getProductSkuById($productId);

            if ($sku === null) {
                continue;
            }

            $inPostPayBestsellerProduct = $this->getInPostPayBestsellerProduct($sku, $websiteId);

            if ($inPostPayBestsellerProduct === null) {
                continue;
            }

            $inPostPayBestsellerProduct->setError($error);

            try {
                $this->inPostPayBestsellerProductRepository->save($inPostPayBestsellerProduct);
            } catch (CouldNotSaveException $e) {
                $this->logger->error(
                    sprintf(
                        'Could not update Bestseller Product [SKU:%s] for website ID: %s with Error [%s]. Reason: %s',
                        $sku,
                        $websiteId,
                        $error,
                        $e->getMessage()
                    )
                );
            }
        }
    }

    /**
     * @param int $productId
     * @return string|null
     */
    private function getProductSkuById(int $productId): ?string
    {
        try {
            $product = $this->productRepository->getById($productId);
            $sku = $product->getSku();
        } catch (NoSuchEntityException $e) {
            $sku = null;
        }

        return $sku;
    }

    /**
     * @param string $sku
     * @param int $websiteId
     * @return InPostPayBestsellerProductInterface|null
     */
    private function getInPostPayBestsellerProduct(string $sku, int $websiteId): ?InPostPayBestsellerProductInterface
    {
        try {
            return $this->inPostPayBestsellerProductRepository->getBySkuAndWebsiteId($sku, $websiteId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
