<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer;

use InPost\InPostPay\Model\IziApi\Response\Data\TransactionItem;
use InPost\InPostPay\Model\IziApi\Response\Data\TransactionItemFactory;
use InPost\InPostPay\Model\IziApi\Response\Data\TransactionItemOperation;
use InPost\InPostPay\Model\IziApi\Response\Data\TransactionItemOperationFactory;
use InPost\InPostPay\Model\IziApi\Response\TransactionListResponse;
use InPost\InPostPay\Model\IziApi\Response\TransactionListResponseFactory;

class TransactionListResponseDataTransfer
{
    public function __construct(
        private readonly TransactionListResponseFactory $transactionListResponseFactory,
        private readonly TransactionItemFactory $transactionItemFactory,
        private readonly TransactionItemOperationFactory $transactionItemOperationFactory,
    ) {
    }

    public function convertToObject(array $responseData): TransactionListResponse
    {
        $transactionItems = [];
        $count = (int)($responseData[TransactionListResponse::COUNT] ?? 0);

        if (array_key_exists(TransactionListResponse::ITEMS, $responseData)) {
            $items = (array)($responseData[TransactionListResponse::ITEMS] ?? []);

            foreach ($items as $item) {
                $transactionItems[] = $this->createTransactionItem($item);
            }
        }

        /** @var TransactionListResponse $transactionListResponse */
        $transactionListResponse = $this->transactionListResponseFactory->create();
        $transactionListResponse->setCount($count);
        $transactionListResponse->setItems($transactionItems);

        return $transactionListResponse;
    }

    /**
     * @param array $itemData
     *
     * @return TransactionItem
     */
    private function createTransactionItem(array $itemData): TransactionItem
    {
        $transactionId = (string)($itemData[TransactionItem::TRANSACTION_ID] ?? '');
        $merchantPosId = (string)($itemData[TransactionItem::MERCHANT_POS_ID] ?? '');
        $externalTransactionId = (string)($itemData[TransactionItem::EXTERNAL_TRANSACTION_ID] ?? '');
        $description = (string)($itemData[TransactionItem::DESCRIPTION] ?? '');
        $status = (string)($itemData[TransactionItem::STATUS] ?? '');
        $createdDate = (string)($itemData[TransactionItem::CREATED_DATE] ?? '');
        $amount = (float)($itemData[TransactionItem::AMOUNT] ?? 0.00);
        $currency = (string)($itemData[TransactionItem::CURRENCY] ?? '');
        $paymentMethod = (string)($itemData[TransactionItem::PAYMENT_METHOD] ?? '');
        $orderId = (string)($itemData[TransactionItem::ORDER_ID] ?? '');

        /** @var TransactionItem $transactionItem */
        $transactionItem = $this->transactionItemFactory->create();
        $transactionItem->setTransactionId($transactionId);
        $transactionItem->setMerchantPosId($merchantPosId);
        $transactionItem->setExternalTransactionId($externalTransactionId);
        $transactionItem->setDescription($description);
        $transactionItem->setStatus($status);
        $transactionItem->setCreatedDate($createdDate);
        $transactionItem->setAmount($amount);
        $transactionItem->setCurrency($currency);
        $transactionItem->setPaymentMethod($paymentMethod);
        $transactionItem->setOrderId($orderId);

        if (array_key_exists(TransactionItem::OPERATIONS, $itemData)) {
            $operations = (array)($itemData[TransactionItem::OPERATIONS] ?? []);
            $transactionItemOperations = [];

            foreach ($operations as $operation) {
                $transactionItemOperations[] = $this->createTransactionItemOperation($operation);
            }

            $transactionItem->setOperations($transactionItemOperations);
        }

        return $transactionItem;
    }

    private function createTransactionItemOperation(array $itemOperationData): TransactionItemOperation
    {
        $externalOperationId = (string)($itemOperationData[TransactionItemOperation::EXTERNAL_OPERATION_ID] ?? '');
        $type = (string)($itemOperationData[TransactionItemOperation::TYPE] ?? '');
        $status = (string)($itemOperationData[TransactionItemOperation::STATUS] ?? '');
        $amount = (float)($itemOperationData[TransactionItemOperation::AMOUNT] ?? 0.00);
        $currency = (string)($itemOperationData[TransactionItemOperation::CURRENCY] ?? '');
        $operationDate = (string)($itemOperationData[TransactionItemOperation::OPERATION_DATE] ?? '');

        /** @var TransactionItemOperation $transactionItemOperation */
        $transactionItemOperation = $this->transactionItemOperationFactory->create();
        $transactionItemOperation->setExternalOperationId($externalOperationId);
        $transactionItemOperation->setType($type);
        $transactionItemOperation->setStatus($status);
        $transactionItemOperation->setAmount($amount);
        $transactionItemOperation->setCurrency($currency);
        $transactionItemOperation->setOperationDate($operationDate);

        return $transactionItemOperation;
    }
}
