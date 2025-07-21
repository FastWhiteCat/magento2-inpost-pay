<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

interface OrderProcessorInterface
{
    /**
     * @param Quote $quote
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @param OrderInterface $inPostOrder
     * @return Order
     * @throws LocalizedException
     */
    public function execute(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, OrderInterface $inPostOrder): Order;
}
