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
    private array $dataTransfers = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        array $dataTransfer = []
    ) {
        $this->initDataTransfers($dataTransfer);
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        foreach ($this->dataTransfers as $dataTransfer) {
            $dataTransfer->transfer($quote, $basket);
        }
    }

    /**
     * @param array $dataTransfers
     * @return void
     * @throws InvalidArgumentException
     */
    private function initDataTransfers(array $dataTransfers): void
    {
        foreach ($dataTransfers as $dataTransferKey => $dataTransfer) {
            if ($dataTransfer instanceof QuoteToBasketDataTransferInterface) {
                $this->dataTransfers[$dataTransferKey] = $dataTransfer;
            } else {
                $errorMsg = sprintf('Quote to Basket data transfer: %s is not valid.', $dataTransferKey);
                $this->logger->critical($errorMsg);

                throw new InvalidArgumentException($errorMsg);
            }
        }
    }
}
