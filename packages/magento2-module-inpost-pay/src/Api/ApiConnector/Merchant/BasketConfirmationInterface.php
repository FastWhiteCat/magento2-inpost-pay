<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\BrowserInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;

/**
 * InPost Pay Basket service for confirming bound basket.
 * @api
 */
interface BasketConfirmationInterface
{
    public const QUOTE = 'quote';
    public const BROWSER = 'browser';
    public const BASKET = 'basket';

    /**
     * @param string $basketId
     * @param string|null $status
     * @param string|null $inpostBasketId
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface|null $phoneNumber
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\BrowserInterface|null $browser
     * @param string|null $maskedPhoneNumber
     * @param string|null $name
     * @param string|null $surname
     * @return \InPost\InPostPay\Api\Data\Merchant\BasketInterface
     * @throws \InPost\InPostPay\Exception\InPostPayBadRequestException
     * @throws \InPost\InPostPay\Exception\InPostPayAuthorizationException
     * @throws \InPost\InPostPay\Exception\BasketNotFoundException
     * @throws \InPost\InPostPay\Exception\InPostPayInternalException
     */
    public function execute(
        string $basketId,
        ?string $status = null,
        ?string $inpostBasketId = null,
        ?PhoneNumberInterface $phoneNumber = null,
        ?BrowserInterface $browser = null,
        ?string $maskedPhoneNumber = null,
        ?string $name = null,
        ?string $surname = null
    ): BasketInterface;
}
