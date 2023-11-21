<?php
declare(strict_types=1);

namespace InPost\InPostPay\Api;

interface CheckoutAgreementsVersionRepositoryInterface
{
    /**
     * @param int $agreementId
     * @return int
     */
    public function getCheckoutAgreementVersion(int $agreementId): int;

    /**
     * @param array $data
     * @return void
     */
    public function save(array $data): void;

    /**
     * @param array $ids
     * @return array
     */
    public function getList(array $ids): array;
}
