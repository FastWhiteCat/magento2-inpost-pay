<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

interface BasketInterface
{
    /**
     * @param string $basketId
     * @return array
     */
    public function get(string $basketId): array;

    /**
     * @param string $basketId
     * @return array
     */
    public function update(string $basketId): array;

    /**
     * @param string $basketId
     *
     * @return void
     */
    public function delete(string $basketId): void;
}
