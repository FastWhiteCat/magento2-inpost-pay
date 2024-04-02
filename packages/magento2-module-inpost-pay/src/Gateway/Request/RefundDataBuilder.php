<?php

declare(strict_types=1);

namespace InPost\InPostPay\Gateway\Request;

use InPost\InPostPay\Api\Data\Merchant\RefundInterface;
use InPost\InPostPay\Model\IziApi\Response\TransactionListResponse;
use InPost\InPostPay\Service\ApiConnector\TransactionList;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

class RefundDataBuilder implements BuilderInterface
{
    public function __construct(
        private readonly TransactionList $transactionList,
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly LoggerInterface $logger
    ) {
    }

    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObjectInterface $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);

        /** @var Payment $payment */
        $payment = $paymentDataObject->getPayment();
        /** @var Order $order */
        $order = $payment->getOrder();

        $refundId = uniqid('', true);
        $orderId = $order->getIncrementId();
        $refundAmount = (float)($buildSubject['amount']);
        $refundAdditionalInfo = null;

        $inPostPayTransactionList = $this->transactionList->execute(orderId: $orderId);

        if (!$inPostPayTransactionList instanceof TransactionListResponse) {
            $this->logger->error("Invalid Transaction List response for OrderId: $orderId");
            return [];
        }

        if (empty($inPostPayTransactionList->getItems())) {
            $this->logger->error("Empty InPostPay Transaction list for OrderId: $orderId.");
            return [];
        }

        $refundRequestData = [];
        $merchantTransactions = $this->getMerchantTransactions(
            $payment->getEntityId(),
            $order->getEntityId()
        );
        foreach ($inPostPayTransactionList->getItems() as $transaction) {
            $inPostPayTransactionId = (string)($transaction[RefundInterface::TRANSACTION_ID] ?? '');
            if (!empty($merchantTransactions) && !in_array($inPostPayTransactionId, $merchantTransactions, true)) {
                $this->logger->warning("Missing InPostPay Transaction: $inPostPayTransactionId for OrderId: $orderId.");
                continue;
            }
            $refundRequestData = [
                RefundInterface::TRANSACTION_ID => $inPostPayTransactionId,
                RefundInterface::EXTERNAL_REFUND_ID => $refundId,
                RefundInterface::REFUND_AMOUNT => $refundAmount,
                RefundInterface::ADDITIONAL_BUSINESS_DATA => $refundAdditionalInfo
            ];
        }

        return ['body' => ['refund_request_data' => $refundRequestData]];
    }

    private function getMerchantTransactions(mixed $orderId, mixed $paymentId): array
    {
        $merchantTransactions = [];

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId)
            ->addFilter('payment_id', $paymentId)
            ->addFilter('txn_type', TransactionInterface::TYPE_CAPTURE);

        $transactions = $this->transactionRepository->getList($searchCriteria->create());
        $transactionItems = $transactions->getItems();

        if (empty($transactionItems)) {
            return [];
        }

        foreach ($transactionItems as $transactionItem) {
            $merchantTransactions[] = $transactionItem->getTxnId();
        }

        return $merchantTransactions;
    }
}
