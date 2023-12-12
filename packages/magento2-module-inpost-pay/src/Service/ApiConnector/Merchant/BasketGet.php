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
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class BasketGet implements BasketGetInterface
{
    private const REQUEST_PREFIX = 'BASKET_GET_REQUEST';

    public function __construct(
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
        $this->createRequestDebugLog(sprintf('Retrieving quote data for Basket ID: %s', $basketId));
        $inPostPayQuote = $this->getInPostPayQuoteByBasketId($basketId);
        $quote = $this->getQuoteById($inPostPayQuote->getQuoteId());
        $basket = $this->basketFactory->create();
        $this->quoteToBasketDataTransfer->transfer($quote, $basket);
        $this->createRequestDebugLog(sprintf('Quote data for Basket ID: %s has been retrieved.', $basketId));

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

    private function createRequestDebugLog(string $message): void
    {
        $this->logger->debug(sprintf('%s: %s', self::REQUEST_PREFIX, $message));
    }
}
