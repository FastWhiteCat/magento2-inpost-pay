<?php
declare(strict_types=1);

namespace InPost\InPostPay\Api;

interface WidgetInterface
{
    /**
     * @param string $cartId
     * @param string $bindingPlace
     * @param string $browser
     * @param string|null $prefix
     * @param string|null $phoneNumber
     * @return array
     */
    public function getPayData(
        string $cartId,
        string $bindingPlace,
        string $browser,
        ?string $prefix = null,
        ?string $phoneNumber = null
    ): array;
}
