<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellersInterface;
use InPost\InPostPay\Model\IziApi\Request\GetBestsellersRequest;
use InPost\InPostPay\Model\IziApi\Request\GetBestsellersRequestFactory;
use InPost\InPostPay\Service\Converter\ArrayToBestsellersResultConverter;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class GetBestsellers
{
    private const DEFAULT_PAGE_SIZE = 10;

    /**
     * @param ConnectorInterface $connector
     * @param GetBestsellersRequestFactory $getBestsellersRequestFactory
     * @param ArrayToBestsellersResultConverter $arrayToBestsellerProductConverter
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly GetBestsellersRequestFactory $getBestsellersRequestFactory,
        private readonly ArrayToBestsellersResultConverter $arrayToBestsellerProductConverter,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param int $storeId
     * @return BestsellerProductInterface[]
     * @throws LocalizedException
     */
    public function execute(int $storeId): array
    {
        try {
            return $this->getAllBestsellerProducts($storeId);
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with downloading bestsellers. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }

    /**
     * @param int $storeId
     * @return BestsellerProductInterface[]
     */
    private function getAllBestsellerProducts(int $storeId): array
    {
        $bestsellerProducts = [];
        $pageIndex = 0;

        do {
            /** @var GetBestsellersRequest $request */
            $request = $this->getBestsellersRequestFactory->create();
            $request->setStoreId($storeId);
            $request->setParams(
                [
                    BestsellersInterface::PAGE_INDEX => $pageIndex,
                    BestsellersInterface::PAGE_SIZE => self::DEFAULT_PAGE_SIZE
                ]
            );

            try {
                $resultArray = $this->connector->sendRequest($request);
                $bestsellerResult = $this->arrayToBestsellerProductConverter->convert($resultArray);
                $pageItems = $bestsellerResult->getContent();
            } catch (LocalizedException $e) {
                $pageItems = [];
            }

            $pageIndex++;
            $bestsellerProducts = array_merge($bestsellerProducts, $pageItems);
        } while (!empty($pageItems));

        return $bestsellerProducts;
    }
}
