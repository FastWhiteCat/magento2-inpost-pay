<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\BestsellerProductsGetInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellersInterface;
use InPost\InPostPay\Exception\BestsellerProductNotFoundException;
use InPost\InPostPay\Provider\BestsellersProvider;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Webapi\Request;
use Throwable;
use InPost\InPostPay\Exception\InPostPayAuthorizationException;
use InPost\InPostPay\Exception\InPostPayBadRequestException;
use InPost\InPostPay\Exception\InPostPayInternalException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BestsellerProductsGet implements BestsellerProductsGetInterface
{
    public function __construct(
        private readonly Request $httpRequest,
        private readonly BestsellersProvider $bestsellerProductsProvider,
        private readonly EventManager $eventManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return BestsellersInterface
     * @throws InPostPayBadRequestException
     * @throws InPostPayAuthorizationException
     * @throws BestsellerProductNotFoundException
     * @throws InPostPayInternalException
     */
    public function execute(): BestsellersInterface
    {
        try {
            $pageIndex = $this->httpRequest->getParam(self::PAGE_INDEX_PARAM);
            $pageSize = $this->httpRequest->getParam(self::PAGE_SIZE_PARAM);
            $productId = $this->httpRequest->getParam(self::PRODUCT_ID_PARAM);

            $this->eventManager->dispatch(
                'izi_bestseller_products_get_before',
                [
                    self::PAGE_INDEX_PARAM => $pageIndex,
                    self::PAGE_SIZE_PARAM => $pageSize,
                    self::PRODUCT_ID_PARAM => $productId
                ]
            );

            $response = $this->bestsellerProductsProvider->get(
                is_scalar($pageIndex) ? (int)$pageIndex : 1,
                is_scalar($pageSize) ? (int)$pageSize : 5,
                is_scalar($productId) ? (int)$productId : null
            );

            $this->eventManager->dispatch(
                'izi_bestseller_products_get_after',
                [BestsellerProductsGetInterface::RESPONSE => $response]
            );

            return $response;
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            throw new BestsellerProductNotFoundException();
        } catch (InPostPayAuthorizationException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw new InPostPayBadRequestException();
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage());

            throw new InPostPayInternalException();
        }
    }
}
