<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Model\IziApi\Request\BasketBindingRequest;
use InPost\InPostPay\Model\IziApi\Request\BasketBindingRequestFactory;
use InPost\InPostPay\Service\GetBasketId;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class GetBasketBindingApiKey
{
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly BasketBindingRequestFactory $basketBindingRequestFactory,
        private readonly GetBasketId $getBasketId,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param int $quoteId
     * @return string
     * @throws LocalizedException
     */
    public function execute(int $quoteId): string
    {
        $basketId = $this->getBasketId->get($quoteId, true);

        /** @var BasketBindingRequest $request */
        $request = $this->basketBindingRequestFactory->create();
        $request->setParams([InPostPayQuoteInterface::BASKET_ID => $basketId]);

        try {
            $result = $this->connector->sendRequest($request);
            $basketBindingApiKey = ($result[InPostPayQuoteInterface::BASKET_BINDING_API_KEY] ?? '');

            if (empty($basketBindingApiKey)) {
                throw new LocalizedException(__('Basket binding api key is empty.'));
            }

            return $basketBindingApiKey;
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with binding basket. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }
}
