<?php
declare(strict_types=1);

namespace InPost\InPostPay\Api\Widget\Basket;

/**
 * @api
 */
interface GetGuestPayDataInterface
{
    /**
     * @param string $cartId
     * @param string $bindingPlace
     * @param string $browser
     * @param string|null $prefix
     * @param string|null $phoneNumber
     * @return array
     */
    public function execute(
        string $cartId,
        string $bindingPlace,
        string $browser,
        ?string $prefix = null,
        ?string $phoneNumber = null
    ): array;
}
