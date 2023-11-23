<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

interface BasketEventInterface
{
    /**
     * @param string $basketId
     * @return array
     */
    public function execute(string $basketId): array;
}
