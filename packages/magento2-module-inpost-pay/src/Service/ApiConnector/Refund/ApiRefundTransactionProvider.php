<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Refund;

use InPost\InPostPay\Enum\InPostRefundStatus;
use InPost\InPostPay\Model\IziApi\Response\Data\TransactionItem;
use InPost\InPostPay\Model\IziApi\Response\Data\TransactionItemOperation;
use InPost\InPostPay\Model\IziApi\Response\TransactionRefundResponse;
use InPost\InPostPay\Service\ApiConnector\TransactionList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;

class ApiRefundTransactionProvider
{
    private ?AdapterInterface $connection = null;

    /**
     * @param TransactionList $transactionList
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly TransactionList $transactionList,
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    /**
     * @param string $transactionId
     * @param string $externalRefundId
     * @param bool $forceSleep This flag is only used for error handling when a refund transaction fails to register
     * due to InPost Pay API relatively short Timeout, however, that process could have registered that transaction.
     * If that happens, the sleep and transaction retrieving is implemented to check if that the transaction is found
     *
     * @return array
     * @throws LocalizedException
     */
    public function getRefundTransactionDataByTransactionId(
        string $transactionId,
        string $externalRefundId,
        bool $forceSleep = false
    ): array {
        $orderId = $this->getOrderIdByTransactionId($transactionId);
        $storeId = (int)$this->getOrderStoreId((int)$orderId);
        $orderIncrementId = (string)$this->getOrderNrById((int)$orderId);

        if (empty($orderIncrementId)) {
            throw new LocalizedException(__('Order ID is not found for Transaction ID: %1', $transactionId));
        }

        if ($forceSleep) {
            // Only when handling error, that process should not be triggered in loops
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            sleep(10);
        }

        $inPostPayTransactions = $this->transactionList->execute(
            orderId: $orderIncrementId,
            storeId: $storeId
        )->getItems();

        $apiRefundOperation = null;

        foreach ($inPostPayTransactions as $apiTransaction) {
            /** @var TransactionItem $apiTransaction */
            if ($apiTransaction->getTransactionId() === $transactionId && !empty($apiTransaction->getOperations())) {
                $apiRefundOperation = $this->findRefundOperationStatusByExternalId(
                    $apiTransaction->getOperations(),
                    $externalRefundId
                );
            }
        }

        if ($apiRefundOperation === null) {
            $description = __(
                'InPost Pay API endpoint resulted in error, refund transaction has not been found.'
            )->render();
            $description .= ' ';
            $description .= __('Creditmemo will be created with pending status and confirmed via webhook.')->render();

            return [TransactionRefundResponse::DESCRIPTION => $description];
        } elseif ($apiRefundOperation->getStatus() === InPostRefundStatus::PENDING->value) {
            $description = __(
                'InPost Pay API endpoint resulted in error, refund transaction has been registered as pending.'
            )->render();
            $description .= ' ';
            $description .= __('Creditmemo will be created with pending status and confirmed via webhook.')->render();

            return [
                TransactionRefundResponse::EXTERNAL_REFUND_ID => $apiRefundOperation->getExternalOperationId(),
                TransactionRefundResponse::STATUS => $apiRefundOperation->getStatus(),
                TransactionRefundResponse::REFUND_AMOUNT => abs($apiRefundOperation->getAmount()),
                TransactionRefundResponse::DESCRIPTION => $description
            ];
        } elseif ($apiRefundOperation->getStatus() === InPostRefundStatus::SUCCESS->value) {
            $description = __(
                'InPost Pay API endpoint resulted in error but refund transaction has been successfully processed.'
            )->render();
            $description .= ' ';
            $description .= __(
                'Creditmemo will be created with success status and does not require confirmation via webhook.'
            )->render();

            return [
                TransactionRefundResponse::EXTERNAL_REFUND_ID => $apiRefundOperation->getExternalOperationId(),
                TransactionRefundResponse::STATUS => $apiRefundOperation->getStatus(),
                TransactionRefundResponse::REFUND_AMOUNT => abs($apiRefundOperation->getAmount()),
                TransactionRefundResponse::DESCRIPTION => $description
            ];
        } else {
            $description = __(
                'InPost Pay API endpoint resulted in error, but refund transaction was found with unsuccessful status.'
            )->render();
            $description .= ' ';
            $description .= __(
                'Creditmemo will not be created.'
            )->render();

            throw new LocalizedException(__($description));
        }
    }

    /**
     * @param TransactionItemOperation[] $operations
     * @param string $externalRefundId
     * @return TransactionItemOperation|null
     */
    private function findRefundOperationStatusByExternalId(
        array $operations,
        string $externalRefundId
    ): ?TransactionItemOperation {
        $apiOperation = null;

        foreach ($operations as $operation) {
            if ($operation->getExternalOperationId() === $externalRefundId) {
                $apiOperation = $operation;
                break;
            }
        }

        return $apiOperation;
    }

    /**
     * Get order ID by transaction ID
     *
     * @param string $txnId
     * @return int|null
     */
    private function getOrderIdByTransactionId(string $txnId): ?int
    {
        $tableName = $this->getConnection()->getTableName('sales_payment_transaction');
        $select = $this->getConnection()->select()
            ->from($tableName, ['order_id'])
            ->where('txn_id = ?', $txnId)
            ->limit(1);

        $orderId = $this->getConnection()->fetchOne($select);

        return $orderId ? (int)$orderId : null;
    }

    /**
     * Get order Increment ID by Entity ID
     *
     * @param int $orderId
     * @return string|null
     */
    private function getOrderNrById(int $orderId): ?string
    {
        $tableName = $this->getConnection()->getTableName('sales_order');
        $select = $this->getConnection()->select()
            ->from($tableName, ['increment_id'])
            ->where('entity_id = ?', $orderId)
            ->limit(1);

        $orderIncrementId = $this->getConnection()->fetchOne($select);

        return $orderIncrementId ? (string)$orderIncrementId : null;
    }

    /**
     * @param int $orderId
     * @return int|null
     */
    private function getOrderStoreId(int $orderId): ?int
    {
        $tableName = $this->getConnection()->getTableName('sales_order');
        $select = $this->getConnection()->select()
            ->from($tableName, ['store_id'])
            ->where('entity_id = ?', $orderId)
            ->limit(1);

        $storeId = $this->getConnection()->fetchOne($select);

        return $storeId ? (int)$storeId : null;
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resourceConnection->getConnection();
        }

        return $this->connection;
    }
}
