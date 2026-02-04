<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Model\IziApi\Request\PutBestsellerRequest;
use InPost\InPostPay\Model\IziApi\Request\PutBestsellerRequestFactory;
use InPost\InPostPay\Service\Converter\InPostBestsellerProductToArrayConverter;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class PutBestseller
{
    /**
     * @param ConnectorInterface $connector
     * @param PutBestsellerRequestFactory $putBestsellerRequestFactory
     * @param InPostBestsellerProductToArrayConverter $inPostBestsellerProductToArrayConverter
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly PutBestsellerRequestFactory $putBestsellerRequestFactory,
        private readonly InPostBestsellerProductToArrayConverter $inPostBestsellerProductToArrayConverter,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param BestsellerProductInterface $bestsellerProduct
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     */
    public function execute(BestsellerProductInterface $bestsellerProduct, int $storeId): array
    {
        /** @var PutBestsellerRequest $putBestsellerRequest */
        $putBestsellerRequest = $this->putBestsellerRequestFactory->create();
        $bestsellerProductData = $this->inPostBestsellerProductToArrayConverter->convert($bestsellerProduct);
        $putBestsellerRequest->setStoreId($storeId);
        $putBestsellerRequest->setParams($bestsellerProductData);

        try {
            return $this->connector->sendRequest($putBestsellerRequest);
        } catch (LocalizedException $e) {
            $errorMsg = __('There was a problem with uploading bestseller update. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }
}
