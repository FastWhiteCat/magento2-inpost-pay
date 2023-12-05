<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api;

use InPost\InPostPay\Model\Dto\Order as OrderDto;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

interface OrderProcessorInterface
{
    /**
     * @param Quote $quote
     * @param OrderDto $orderDto
     * @return Order
     * @throws LocalizedException
     */
    public function execute(Quote $quote, OrderDto $orderDto): Order;
}
