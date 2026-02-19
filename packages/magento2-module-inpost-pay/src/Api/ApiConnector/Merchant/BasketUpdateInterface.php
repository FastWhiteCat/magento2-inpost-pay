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
    public const EVENT_ID = 'event_id';
    public const EVENT_DATA_TIME = 'event_data_time';
    public const EVENT_TYPE = 'event_type';
    public const QUANTITY_EVENT_DATA = 'quantity_event_data';
    public const RELATED_PRODUCTS_EVENT_DATA = 'related_products_event_data';
    public const PROMO_CODES_EVENT_DATA = 'promo_codes_event_data';

    /**
     * @param string $basketId
     * @param string $eventId
     * @param string $eventDataTime
     * @param string $eventType
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\QuantityUpdateInterface[]|null $quantityEventData
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\QuantityUpdateInterface[]|null $relatedProductsEventData
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterface[]|null $promoCodesEventData
     * @return \InPost\InPostPay\Api\Data\Merchant\BasketInterface
     * @throws \InPost\InPostPay\Exception\InPostPayBadRequestException
     * @throws \InPost\InPostPay\Exception\InPostPayAuthorizationException
     * @throws \InPost\InPostPay\Exception\BasketNotFoundException
     * @throws \InPost\InPostPay\Exception\InPostPayInternalException
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
