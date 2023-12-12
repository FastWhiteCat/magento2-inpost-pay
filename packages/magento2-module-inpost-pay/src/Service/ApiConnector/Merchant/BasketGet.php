<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketGetInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface as BasketDataInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Service\DataTransfer\QuoteToBasketDataTransfer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Base64Json as Base64JsonSerializer;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class BasketGet implements BasketGetInterface
{
    private const REQUEST_PREFIX = 'BASKET_GET_REQUEST';
    private const RESPONSE_PREFIX = 'BASKET_GET_RESPONSE';

    public function __construct(
        private readonly Base64JsonSerializer $base64JsonSerializer,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly QuoteToBasketDataTransfer $quoteToBasketDataTransfer,
        private readonly BasketInterfaceFactory $basketFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param string $basketId
     * @return BasketDataInterface
     * @throws LocalizedException
     */
    public function execute(string $basketId): BasketDataInterface
    {
        $logMessage = sprintf('Quote data for Basket ID: %s', $basketId);
        $this->createRequestDebugLog(self::REQUEST_PREFIX, $logMessage);

        $inPostPayQuote = $this->getInPostPayQuoteByBasketId($basketId);
        $quote = $this->getQuoteById($inPostPayQuote->getQuoteId());
        $basket = $this->basketFactory->create();
        $this->quoteToBasketDataTransfer->transfer($quote, $basket);

        $this->createRequestDebugLog(self::RESPONSE_PREFIX, $logMessage, $basket->getData());

        return $basket;
    }

    /**
     * @param string $basketId
     * @return InPostPayQuoteInterface
     * @throws LocalizedException
     */
    private function getInPostPayQuoteByBasketId(string $basketId): InPostPayQuoteInterface
    {
        try {
            return $this->inPostPayQuoteRepository->getByBasketId($basketId);
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    /**
     * @param int $quoteId
     * @return Quote
     * @throws LocalizedException
     */
    private function getQuoteById(int $quoteId): Quote
    {
        try {
            $quote = $this->cartRepository->get($quoteId);

            if ($quote instanceof Quote) {
                return $quote;
            } else {
                throw new LocalizedException(__('Quote with ID %1 is invalid.', $quoteId));
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    private function createRequestDebugLog(string $logPrefix, string $message, array $data = []): void
    {
        $serializedData = ($data) ? $this->base64JsonSerializer->serialize($data) : '';
        $dataLabel = ($logPrefix === self::REQUEST_PREFIX) ? 'Payload' : 'Response';
        $this->logger->debug(sprintf('%s: %s %s: %s', $logPrefix, $message, $dataLabel, $serializedData));
    }
}
