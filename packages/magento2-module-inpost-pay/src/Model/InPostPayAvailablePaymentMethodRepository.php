<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use Exception;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use InPost\InPostPay\Api\Data\InPostPayAvailablePaymentMethodInterface;
use InPost\InPostPay\Api\Data\InPostPayAvailablePaymentMethodInterfaceFactory;
use InPost\InPostPay\Api\InPostPayAvailablePaymentMethodRepositoryInterface;
use InPost\InPostPay\Model\ResourceModel\InPostPayAvailablePaymentMethod as InPostPayAvailablePaymentMethodResource;
use InPost\InPostPay\Model\ResourceModel\InPostPayAvailablePaymentMethod\CollectionFactory;

class InPostPayAvailablePaymentMethodRepository implements InPostPayAvailablePaymentMethodRepositoryInterface
{
    public function __construct(
        private readonly InPostPayAvailablePaymentMethodResource $resource,
        private readonly InPostPayAvailablePaymentMethodFactory $inPostPayAvailablePaymentMethodInterfaceFactory
    ) {
    }

    public function save(InPostPayAvailablePaymentMethodInterface $paymentMethod): InPostPayAvailablePaymentMethodInterface
    {
        try {
            // @phpstan-ignore-next-line
            $this->resource->save($paymentMethod);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not save Payment method: %1', $e->getMessage()));
        }

        return $paymentMethod;
    }

    public function get(int $id): InPostPayAvailablePaymentMethodInterface
    {
        $inPostPayBasketNotice = $this->inPostPayAvailablePaymentMethodInterfaceFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load($inPostPayBasketNotice, $id);
        try {
            $inPostPayBasketNotice->getBasketNoticeId();
        } catch (LocalizedException $e) {
            throw new NoSuchEntityException(
                __('Payment code with ID "%1" does not exist.', $id)
            );
        }

        return $inPostPayBasketNotice;
    }

    public function getByPaymentCode(string $paymentCode): InPostPayAvailablePaymentMethodInterface
    {
        $inPostPayBasketNotice = $this->inPostPayAvailablePaymentMethodInterfaceFactory->create();

        $this->resource->load(
            // @phpstan-ignore-next-line
            $inPostPayBasketNotice,
            $paymentCode,
            InPostPayAvailablePaymentMethodInterface::PAYMENT_CODE
        );

        try {
            $inPostPayBasketNotice->getBasketNoticeId();
        } catch (LocalizedException $e) {
            throw new NoSuchEntityException(
                __('Payment code with code "%1" does not exist.', $paymentCode)
            );
        }

        return $inPostPayBasketNotice;
    }

    public function getAllValuesAsArray(): array
    {
        return $this->resource->getAllValuesAsArray();
    }

    public function deleteAll(): bool
    {
        $this->resource->deleteAll();

        return true;
    }

    public function insertMultiple(array $data): bool
    {
        $this->resource->insertMultiple($data);

        return true;
    }
}
