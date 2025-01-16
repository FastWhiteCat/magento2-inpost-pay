<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Exception\CouldNotDeleteInPostPayBestsellerProductException;
use InPost\InPostPay\Model\IziApi\Request\DeleteBestsellerRequest;
use InPost\InPostPay\Model\IziApi\Request\DeleteBestsellerRequestFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class DeleteBestseller
{
    /**
     * @param ConnectorInterface $connector
     * @param DeleteBestsellerRequestFactory $deleteBestsellerRequestFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly DeleteBestsellerRequestFactory $deleteBestsellerRequestFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @param bool|null $skipErrors
     * @return void
     * @throws CouldNotDeleteInPostPayBestsellerProductException
     */
    public function deleteBestsellerByProductId(int $productId, int $storeId, ?bool $skipErrors = false): void
    {
        /** @var DeleteBestsellerRequest $request */
        $request = $this->deleteBestsellerRequestFactory->create();
        $request->setParams([BestsellerProductInterface::PRODUCT_ID => $productId]);
        $request->setStoreId($storeId);

        try {
            $this->connector->sendRequest($request);
        } catch (LocalizedException $e) {
            $this->logger->error(
                sprintf(
                    'Could not delete Bestseller [Product ID:%s] from InPost Pay API. Reason: %s',
                    $productId,
                    $e->getMessage()
                )
            );

            $errorMsg = __(
                'Could not delete Bestseller [Product ID:%1] from InPost Pay API. Reason: %2.',
                $productId,
                $e->getMessage()
            );
            $instruction = __('Please log in to InPost Pay Panel and delete this Bestseller Product manually.');

            if (!$skipErrors) {
                throw new CouldNotDeleteInPostPayBestsellerProductException(__('%1 %2', $errorMsg, $instruction));
            }
        }
    }
}
