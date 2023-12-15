<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api;

use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

interface OrderProcessingStepInterface
{
    /**
     * @param Quote $quote
     * @param OrderInterface $inPostOrder
     * @return void
     * @throws LocalizedException
     */
    public function process(Quote $quote, OrderInterface $inPostOrder): void;

    public function getStepCode(): string;
    public function setStepCode(string $stepCode): void;
}
