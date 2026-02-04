<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Model\IziApi\Request\PaymentMethodsRequest;
use InPost\InPostPay\Model\IziApi\Request\PaymentMethodsRequestFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class PaymentMethods
{
    public function __construct(
        private readonly ConnectorInterface            $connector,
        private readonly PaymentMethodsRequestFactory  $paymentMethodsRequestFactory,
        private readonly LoggerInterface               $logger
    ) {
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function execute(): array
    {
        /** @var PaymentMethodsRequest $request */
        $request = $this->paymentMethodsRequestFactory->create();

        try {
            return $this->connector->sendRequest($request);
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with getting payment methods. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }
}
