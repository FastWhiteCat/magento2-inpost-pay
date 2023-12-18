<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\ApiConnector\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\QuantityUpdateInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;

/**
 * InPost Pay Basket service that allows for updating basket data.
 * @api
 */
interface BasketUpdateInterface
{
    /**
     * @param string $basketId
     * @param string $eventId
     * @param string $eventDataTime
     * @param string $eventType
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\QuantityUpdateInterface[]|null $quantityEventData
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\QuantityUpdateInterface[]|null $relatedProductsEventData
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterface[]|null $promoCodesEventData
     * @return \InPost\InPostPay\Api\Data\Merchant\BasketInterface
     */
    public function execute(
        string $basketId,
        string $eventId,
        string $eventDataTime,
        string $eventType,
        ?array $quantityEventData = null,
        ?array $relatedProductsEventData = null,
        ?array $promoCodesEventData = null,
    ): BasketInterface;
}
