<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data;

interface InPostPayCheckoutAgreementStoreInterface
{
    public const TABLE_NAME = 'inpost_pay_checkout_agreement_store';
    public const ENTITY_NAME = 'inpost_pay_checkout_agreement_store';
    public const ENTITY_ID = 'entity_id';
    public const AGREEMENT_ID = 'agreement_id';
    public const STORE_ID = 'store_id';

    /**
     * Get agreement ID.
     *
     * @return int
     */
    public function getAgreementId(): int;

    /**
     * Set agreement ID.
     *
     * @param int $agreementId
     * @return void
     */
    public function setAgreementId(int $agreementId): void;

    /**
     * Get store ID.
     *
     * @return int
     */
    public function getStoreId(): int;

    /**
     * Set store ID.
     *
     * @param int $storeId
     * @return void
     */
    public function setStoreId(int $storeId): void;
}
