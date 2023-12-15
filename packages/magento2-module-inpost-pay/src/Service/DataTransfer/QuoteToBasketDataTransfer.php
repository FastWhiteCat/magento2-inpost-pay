<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer;

use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InvalidArgumentException;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class QuoteToBasketDataTransfer
{
    /**
     * @var QuoteToBasketDataTransferInterface[]
     */
    private array $converters = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        array $converters = []
    ) {
        $this->initConverters($converters);
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        foreach ($this->converters as $converter) {
            $converter->transfer($quote, $basket);
        }
    }

    /**
     * @param array $converters
     * @return void
     * @throws InvalidArgumentException
     */
    private function initConverters(array $converters): void
    {
        foreach ($converters as $converterKey => $converter) {
            if ($converter instanceof QuoteToBasketDataTransferInterface) {
                $this->converters[$converterKey] = $converter;
            } else {
                $errorMsg = sprintf('Quote to Basket converter: %s is not valid.', $converterKey);
                $this->logger->critical($errorMsg);

                throw new InvalidArgumentException($errorMsg);
            }
        }
    }
}
