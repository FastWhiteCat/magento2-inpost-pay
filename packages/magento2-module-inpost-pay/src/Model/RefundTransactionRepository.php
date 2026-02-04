<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use Exception;
use InPost\InPostPay\Api\Data\RefundTransactionInterface;
use InPost\InPostPay\Api\RefundTransactionRepositoryInterface;
use InPost\InPostPay\Model\ResourceModel\RefundTransaction as RefundTransactionResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class RefundTransactionRepository implements RefundTransactionRepositoryInterface
{
    public function __construct(
        private readonly RefundTransactionResource $resource,
        private readonly RefundTransactionFactory $modelFactory
    ) {
    }

    public function save(RefundTransactionInterface $entity): RefundTransactionInterface
    {
        try {
            // @phpstan-ignore-next-line
            $this->resource->save($entity);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not save InPost Pay refund transaction: %1', $e->getMessage()));
        }

        return $entity;
    }

    public function getByExternalTransactionId(string $externalId): RefundTransactionInterface
    {
        $model = $this->modelFactory->create();
        // @phpstan-ignore-next-line
        $this->resource->load(
            $model,
            $externalId,
            RefundTransactionInterface::REFUND_EXTERNAL_TRANSACTION_ID
        );

        if (!$model->getData(RefundTransactionInterface::REFUND_EXTERNAL_TRANSACTION_ID)) {
            throw new NoSuchEntityException(
                __('InPost Pay Refund transaction with external id "%1" does not exist.', $externalId)
            );
        }

        return $model;
    }
}
