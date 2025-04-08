<?php

declare(strict_types=1);

namespace InPost\InPostPay\Gateway\Request;

use InPost\InPostPay\Api\Data\Merchant\RefundInterface;
use InPost\InPostPay\Model\IziApi\Response\TransactionListResponse;
use InPost\InPostPay\Service\ApiConnector\TransactionList;
use InPost\InPostPay\Service\UuidGenerator;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\TransactionInterface;
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
        private readonly UuidGenerator $uuidGenerator,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param array $buildSubject
     * @return array|array[]
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        /** @var PaymentDataObjectInterface $paymentDataObject */
        $paymentDataObject = SubjectReader::readPayment($buildSubject);

        /** @var Payment $payment */
        $payment = $paymentDataObject->getPayment();
        $creditmemo = $payment->getCreditmemo();

        if (!$creditmemo?->getDoTransaction()) {
            return ['body' => ['refund_request_data' => []]];
        }

        /** @var Order $order */
        $order = $payment->getOrder();

        $refundId = $this->uuidGenerator->uuidv4();
        $orderId = $order->getIncrementId();
        $storeId = is_scalar($order->getStoreId()) ? (int)$order->getStoreId() : 0;
        $refundAmount = (float)($buildSubject['amount']);
        $refundAdditionalInfo = null;

        $inPostPayTransactionList = $this->transactionList->execute(orderId: $orderId, storeId: $storeId);

        if (empty($inPostPayTransactionList->getItems())) {
            $errorMsg = __('Empty InPost Pay Transaction list for OrderId: %1', $orderId);
            $this->logger->error($errorMsg->render());

            throw new LocalizedException($errorMsg);
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

            if ($inPostPayTransactionId) {
                $refundRequestData = [
                    RefundInterface::TRANSACTION_ID => $inPostPayTransactionId,
                    RefundInterface::EXTERNAL_REFUND_ID => $refundId,
                    RefundInterface::REFUND_AMOUNT => $refundAmount,
                    RefundInterface::ADDITIONAL_BUSINESS_DATA => $refundAdditionalInfo,
                    'store_id' => $storeId
                ];
                break;
            }
        }

        if (empty($refundRequestData)) {
            throw new LocalizedException(__('No transactions found to be refunded for Order #%1.', $orderId));
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
