<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api;

use InPost\InPostPay\Model\Dto\Order as OrderDto;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

interface OrderProcessingStepInterface
{
    /**
     * @param Quote $quote
     * @param OrderDto $orderDto
     * @return void
     * @throws LocalizedException
     */
    public function process(Quote $quote, OrderDto $orderDto): void;

    public function getStepCode(): string;
    public function setStepCode(string $stepCode): void;
}
