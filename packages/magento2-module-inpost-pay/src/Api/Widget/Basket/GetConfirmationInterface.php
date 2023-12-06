<?php

namespace InPost\InPostPay\Api\Widget\Basket;

/**
 * @api
 */
interface GetConfirmationInterface
{
    /**
     * @param int $cartId
     *
     * @return \InPost\InPostPay\Api\Widget\Basket\ConfirmationInterface
     */
    public function execute(int $cartId): \InPost\InPostPay\Api\Widget\Basket\ConfirmationInterface;
}
