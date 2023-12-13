<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Model\IziApi\Request\BasketBindingDeleteRequest;
use InPost\InPostPay\Model\IziApi\Request\BasketBindingDeleteRequestFactory;
use InPost\InPostPay\Model\IziApi\Request\BasketBindingRequestFactory;
use InPost\InPostPay\Model\IziApi\Request\BasketBindingVerifyRequestFactory;
use InPost\InPostPay\Model\IziApi\Response\BasketInformationResponseFactory;
use Magento\Framework\Exception\LocalizedException;
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
        bool $ifBasketRealized = false
    ):void {
        /** @var BasketBindingDeleteRequest $request */
        $request = $this->basketBindingDeleteRequestFactory->create();

        $params = [];
        $params['basket_id'] = $basketId;
        if ($ifBasketRealized) {
            $params['if_basket_realized'] = $ifBasketRealized;
        }

        $request->setParams($params);

        try {
            $this->connector->sendRequest($request);
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with delete basket binding. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }
}
