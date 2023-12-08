<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

interface OrderEventInterface
{
    /**
     * @param string $orderId
     * @return \InPost\InPostPay\Api\Data\UpdateOrderResponseInterface
     */
    public function execute(string $orderId): \InPost\InPostPay\Api\Data\UpdateOrderResponseInterface;
}
