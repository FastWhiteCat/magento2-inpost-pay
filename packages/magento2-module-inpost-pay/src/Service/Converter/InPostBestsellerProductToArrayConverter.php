<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter;

use Exception;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Psr\Log\LoggerInterface;

class InPostBestsellerProductToArrayConverter
{
    public function __construct(
        private readonly ExtensibleDataObjectConverter $dataObjectConverter,
        private readonly LoggerInterface $logger
    ) {
    }

    public function convert(BestsellerProductInterface $bestseller): array
    {
        try {
            // @phpstan-ignore-next-line
            $data = $this->dataObjectConverter->toNestedArray($bestseller, [], BestsellerProductInterface::class);
        } catch (Exception $e) {
            $this->logger->error(
                sprintf('Could not convert Bestseller Product to array. Reason: %s', $e->getMessage())
            );

            $data = [];
        }

        if (empty($data[BestsellerProductInterface::ADDITIONAL_PRODUCT_IMAGES])) {
            unset($data[BestsellerProductInterface::ADDITIONAL_PRODUCT_IMAGES]);
        }

        return $data;
    }
}
