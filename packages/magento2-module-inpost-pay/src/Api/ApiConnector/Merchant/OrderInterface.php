<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

interface OrderInterface
{
    /**
     * @return array
     */
    public function create(): array;
}
