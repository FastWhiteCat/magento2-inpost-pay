<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Model\IziApi\Request\BasketBindingDeleteRequest;
use InPost\InPostPay\Model\IziApi\Request\BasketBindingDeleteRequestFactory;
use InPost\InPostPay\Model\IziApi\Request\BasketBindingVerifyRequestFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Psr\Log\LoggerInterface;

class BasketBindingDelete
{
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly BasketBindingDeleteRequestFactory $basketBindingDeleteRequestFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(
        string $basketId,
        bool $ifBasketRealized = false,
        bool $isConfirmedBasket = false
    ): void {
        /** @var BasketBindingDeleteRequest $request */
        $request = $this->basketBindingDeleteRequestFactory->create();

        $params = [];
        $params['basket_id'] = $basketId;
        if ($ifBasketRealized) {
            $params['if_basket_realized'] = $ifBasketRealized;
        }

        $request->setParams($params);

        $silencedErrorCodes = $isConfirmedBasket ? [] : [404];

        try {
            $this->connector->sendRequest($request, $silencedErrorCodes);
        } catch (NotFoundException $e) {
            if ($isConfirmedBasket) {
                $errorMsg = __('There was a problem with delete basket binding. Details: %1', $e->getMessage());
                $this->logger->critical($errorMsg->render());
            } else {
                $this->logger->debug(
                    sprintf(
                        'Basket binding delete returned 404 for basket ID %s — no binding existed.',
                        $basketId
                    )
                );
            }
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with delete basket binding. Details: %1', $e->getMessage());
            if ($isConfirmedBasket) {
                $this->logger->critical($errorMsg->render());
                throw new LocalizedException($errorMsg);
            } else {
                $this->logger->debug(
                    sprintf(
                        'Basket binding delete failed for unconfirmed basket ID %s — %s',
                        $basketId,
                        $e->getMessage()
                    )
                );
            }
        }
    }
}
