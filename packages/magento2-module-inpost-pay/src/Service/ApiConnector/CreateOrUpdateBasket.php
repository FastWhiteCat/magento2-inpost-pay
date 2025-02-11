<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Service\Converter\InPostBasketToArrayConverter;
use InPost\InPostPay\Api\Data\Merchant\BasketInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Service\DataTransfer\QuoteToBasketDataTransfer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use InPost\InPostPay\Model\IziApi\Request\BasketRequestFactory;
use InPost\InPostPay\Model\IziApi\Request\BasketRequest;
use InPost\InPostPay\Model\IziApi\Response\BasketResponseFactory;
use InPost\InPostPay\Model\IziApi\Response\BasketResponse;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use Psr\Log\LoggerInterface;

class CreateOrUpdateBasket
{
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly BasketRequestFactory $basketRequestFactory,
        private readonly BasketResponseFactory $basketResponseFactory,
        private readonly BasketInterfaceFactory $basketFactory,
        private readonly QuoteToBasketDataTransfer $quoteToBasketDataTransfer,
        private readonly InPostBasketToArrayConverter $inPostBasketToArrayConverter,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Quote $quote
     * @param string $basketId
     * @return BasketResponse
     * @throws LocalizedException
     */
    public function execute(Quote $quote, string $basketId): BasketResponse
    {
        /** @var BasketRequest $request */
        $request = $this->basketRequestFactory->create();
        $request->setStoreId($quote->getStoreId());

        /** @var BasketInterface $basket */
        $basket = $this->basketFactory->create();
        $basket->setBasketId($basketId);
        $this->quoteToBasketDataTransfer->transfer($quote, $basket);
        $basketData = $this->inPostBasketToArrayConverter->convert($basket);

        $request->setParams($basketData);

        try {
            $result = $this->connector->sendRequest($request);

            return $this->handle($result);
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with basket request. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }

    private function handle(array $result): BasketResponse
    {
        $basketId = (string)($result[BasketResponse::BASKET_ID] ?? '');

        /** @var BasketResponse $basketResponse */
        $basketResponse = $this->basketResponseFactory->create();
        $basketResponse->setBasketId($basketId);

        return $basketResponse;
    }
}
