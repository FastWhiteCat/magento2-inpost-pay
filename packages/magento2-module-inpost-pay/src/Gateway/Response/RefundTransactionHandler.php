<?php

declare(strict_types=1);

namespace InPost\InPostPay\Gateway\Response;

use InPost\InPostPay\Enum\InPostRefundStatus;
use InPost\InPostPay\Model\IziApi\Response\TransactionRefundResponse;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

class RefundTransactionHandler implements HandlerInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Handles transaction id
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDataObject = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDataObject->getPayment();
        $creditmemo = $payment->getCreditmemo();

        $refundResponse = $response['body'] ?? null;
        if (!$refundResponse instanceof TransactionRefundResponse) {
            $this->logger->error('Invalid Transaction Refund response.');
            return;
        }

        $refundResponseDescription = $refundResponse->getDescription();
        $refundResponseStatus = $refundResponse->getStatus();
        $mappedCreditmemoStatus = $this->getMappedCreditmemoStatus($refundResponseStatus);
        if ($refundResponseStatus === InPostRefundStatus::FAILED->value) {
            $this->logger->error(
                'Transaction Refund status failed, external_refund_id: ' . $refundResponse->getExternalRefundId() .
                ', description: ' . $refundResponse->getDescription()
            );
            return;
        }

        $creditmemoCommentData = [
            __("InPost Pay Transaction Refund."),
            __("External Refund Id: %1", $refundResponse->getExternalRefundId())->render(),
            __("Status: %1", $refundResponseStatus)->render(),
            __("Description: %1", $refundResponseDescription)->render()
        ];

        $creditmemo->addComment(implode(PHP_EOL, $creditmemoCommentData));
        $creditmemo->setCreditmemoStatus($mappedCreditmemoStatus);
    }

    private function getMappedCreditmemoStatus(string $refundResponseStatus): int
    {
        return match ($refundResponseStatus) {
            InPostRefundStatus::PENDING->value => Creditmemo::STATE_OPEN,
            InPostRefundStatus::SUCCESS->value => Creditmemo::STATE_REFUNDED,
            default => Creditmemo::STATE_CANCELED,
        };
    }
}
