<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter;

use Exception;
use InPost\InPostPay\Api\Data\Merchant\RefundInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Psr\Log\LoggerInterface;

class InPostRefundToArrayConverter
{
    public function __construct(
        private readonly ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        private readonly LoggerInterface $logger
    ) {
    }

    public function convert(RefundInterface $refund): array
    {
        try {
            // @phpstan-ignore-next-line
            $data = $this->extensibleDataObjectConverter->toNestedArray($refund, [], RefundInterface::class);
        } catch (Exception $e) {
            $this->logger->error(
                sprintf('Could not convert Refund data to array. Reason: %s', $e->getMessage())
            );
            $data = [];
        }

        return $data;
    }
}
