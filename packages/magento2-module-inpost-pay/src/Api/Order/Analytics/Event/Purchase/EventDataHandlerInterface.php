<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Order\Analytics\Event\Purchase;

use InPost\InPostPay\Exception\UnableToSendInPostPayAnalyticsDataException;

interface EventDataHandlerInterface
{
    /**
     * @return string
     */
    public function getEventCode(): string;

    /**
     * @param array $eventData
     * @param int $storeId
     * @return void
     * @throws UnableToSendInPostPayAnalyticsDataException
     */
    public function send(array $eventData, int $storeId): void;
}
