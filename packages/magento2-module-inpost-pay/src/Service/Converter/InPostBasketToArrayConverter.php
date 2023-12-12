<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter;

use Exception;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Psr\Log\LoggerInterface;

class InPostBasketToArrayConverter
{
    public function __construct(
        private readonly ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        private readonly LoggerInterface $logger
    ) {
    }

    public function convert(BasketInterface $basket): array
    {
        try {
            // @phpstan-ignore-next-line
            $data = $this->extensibleDataObjectConverter->toNestedArray($basket, [], BasketInterface::class);
        } catch (Exception $e) {
            $this->logger->error(sprintf('Could not convert Basket to array. Reason: %s', $e->getMessage()));
            $data = [];
        }

        return $data;
    }
}
