<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter;

use Exception;
use InPost\InPostPay\Api\Data\Merchant\Refund\AdditionalBusinessDataInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Psr\Log\LoggerInterface;

class InPostRefundAdditionalDataToArrayConverter
{
    public function __construct(
        private readonly ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        private readonly LoggerInterface $logger
    ) {
    }

    public function convert(AdditionalBusinessDataInterface $additionalData): array
    {
        try {
            // @phpstan-ignore-next-line
            $data = $this->extensibleDataObjectConverter
                ->toNestedArray($additionalData, [], AdditionalBusinessDataInterface::class);
        } catch (Exception $e) {
            $this->logger->error(
                sprintf('Could not convert Refund Additional data to array. Reason: %s', $e->getMessage())
            );
            $data = [];
        }

        return $data;
    }
}
