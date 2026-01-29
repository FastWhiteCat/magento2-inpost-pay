<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data;

interface RefundTransactionInterface
{
    public const MAPPING_ID = 'mapping_id';
    public const CREDITMEMO_ID = 'creditmemo_id';
    public const REFUND_EXTERNAL_TRANSACTION_ID = 'refund_external_transaction_id';
    public const ENTITY_NAME = 'inpost_pay_refund_transaction';

    public function getMappingId(): int;
    public function setMappingId(int $mappingId): void;

    public function getCreditmemoId(): int;
    public function setCreditmemoId(int $creditmemoId): void;

    public function getRefundExternalTransactionId(): string;
    public function setRefundExternalTransactionId(string $externalTransactionId): void;
}
