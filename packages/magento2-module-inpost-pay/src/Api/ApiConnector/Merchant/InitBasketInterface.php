<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;

/**
 * InPost Pay Basket service for initializing basket alongside with adding product.
 * @api
 */
interface InitBasketInterface
{
    public const QUOTE = 'quote';
    public const BASKET = 'basket';

    /**
     * @param string $productId
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface $phoneNumber
     * @param string|null $basketId
     * @return \InPost\InPostPay\Api\Data\Merchant\BasketInterface
     * @throws \InPost\InPostPay\Exception\InPostPayBadRequestException
     * @throws \InPost\InPostPay\Exception\InPostPayAuthorizationException
     * @throws \InPost\InPostPay\Exception\BasketNotFoundException
     * @throws \InPost\InPostPay\Exception\InPostPayInternalException
     */
    public function execute(
        string $productId,
        PhoneNumberInterface $phoneNumber,
        ?string $basketId = null
    ): BasketInterface;
}
