<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api;

use InPost\InPostPay\Api\Data\RefundTransactionInterface;

interface RefundTransactionRepositoryInterface
{
    public function save(RefundTransactionInterface $entity): RefundTransactionInterface;

    public function getByExternalTransactionId(string $externalId): RefundTransactionInterface;
}
