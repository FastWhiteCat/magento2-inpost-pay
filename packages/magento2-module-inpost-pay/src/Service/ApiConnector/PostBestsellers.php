<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Model\IziApi\Request\PostBestsellersRequest;
use InPost\InPostPay\Model\IziApi\Request\PostBestsellersRequestFactory;
use InPost\InPostPay\Service\Converter\InPostBestsellerProductToArrayConverter;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class PostBestsellers
{
    /**
     * @param ConnectorInterface $connector
     * @param PostBestsellersRequestFactory $postBestsellersRequestFactory
     * @param InPostBestsellerProductToArrayConverter $inPostBestsellerProductToArrayConverter
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly PostBestsellersRequestFactory $postBestsellersRequestFactory,
        private readonly InPostBestsellerProductToArrayConverter $inPostBestsellerProductToArrayConverter,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param array $bestsellerProducts
     * @return array
     * @throws LocalizedException
     */
    public function execute(array $bestsellerProducts): array
    {
        if (empty($bestsellerProducts)) {
            return [];
        }

        /** @var PostBestsellersRequest $postBestsellersRequest */
        $postBestsellersRequest = $this->postBestsellersRequestFactory->create();
        $products = [];

        foreach ($bestsellerProducts as $bestsellerProduct) {
            $bestsellerProductData = $this->inPostBestsellerProductToArrayConverter->convert($bestsellerProduct);

            if ($bestsellerProductData) {
                $products[] = $bestsellerProductData;
            }
        }

        $postBestsellersRequest->setParams(['content' => $products]);

        try {
            return $this->connector->sendRequest($postBestsellersRequest);
        } catch (LocalizedException $e) {
            $errorMsg = __('There was a problem with uploading bestsellers. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }
}
