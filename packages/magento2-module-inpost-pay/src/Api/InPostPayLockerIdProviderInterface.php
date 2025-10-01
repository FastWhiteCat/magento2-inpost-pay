<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface InPostPayLockerIdProviderInterface
{
    public const INPOST_LOCKER_ID_FIELD = 'inpost_locker_id';
    public const INPOST_PICKUP_CARRIER_CODE = 'inpostlocker';

    /**
     * @param int $orderId)
     * @return string|null
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getFromOrderById(int $orderId): ?string;

    /**
     * @param string $orderIncrementId
     * @return string|null
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getFromOrderByIncrementId(string $orderIncrementId): ?string;
}
