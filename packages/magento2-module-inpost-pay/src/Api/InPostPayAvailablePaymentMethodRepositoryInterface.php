<?php
declare(strict_types=1);

namespace InPost\InPostPay\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Api\Data\InPostPayAvailablePaymentMethodInterface;

interface InPostPayAvailablePaymentMethodRepositoryInterface
{
    /**
     * @param InPostPayAvailablePaymentMethodInterface $paymentMethod
     * @return InPostPayAvailablePaymentMethodInterface
     * @throws CouldNotSaveException
     */
    public function save(
        InPostPayAvailablePaymentMethodInterface $paymentMethod
    ): InPostPayAvailablePaymentMethodInterface;

    /**
     * @param int $id
     * @return InPostPayAvailablePaymentMethodInterface
     * @throws NoSuchEntityException
     */
    public function get(int $id): InPostPayAvailablePaymentMethodInterface;

    /**
     * @param string $paymentCode
     * @return InPostPayAvailablePaymentMethodInterface
     * @throws NoSuchEntityException
     */
    public function getByPaymentCode(string $paymentCode): InPostPayAvailablePaymentMethodInterface;

    /**
     * @return array
     */
    public function getAllValuesAsArray(): array;

    /**
     * @return bool
     */
    public function deleteAll(): bool;

    /**
     * @param array $data
     * @return bool
     */
    public function insertMultiple(array $data): bool;
}
