<?php

declare(strict_types=1);

namespace InPost\InPostPay\Gateway\Response;

use InPost\InPostPay\Enum\InPostRefundStatus;
use InPost\InPostPay\Model\IziApi\Response\TransactionRefundResponse;
use Magento\Framework\Exception\LocalizedException;
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
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDataObject = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDataObject->getPayment();
        $creditmemo = $payment->getCreditmemo();

        if ($creditmemo && !$creditmemo->getDoTransaction()) {
            $creditmemo->addComment(__('InPost Pay order offline refund has been initialised.')->render());
            $creditmemo->setState($this->getMappedCreditmemoState(InPostRefundStatus::SUCCESS->value));

            return;
        }

        $this->validResponse($response);

        $refundResponse = $response['body'];
        $refundResponseDescription = $refundResponse->getDescription();
        $refundResponseStatus = $refundResponse->getStatus();
        $externalRefundId = $refundResponse->getExternalRefundId();

        if ($refundResponseStatus === InPostRefundStatus::FAILED->value) {
            $errorMsg = __(
                'InPost Pay API returned transaction refund status: failed. External Refund ID: %1 Description: %2',
                $externalRefundId,
                $refundResponse->getDescription()
            );
            $this->logger->error($errorMsg->getText());

            throw new LocalizedException($errorMsg);
        }

        $creditmemoCommentData = [
            __('InPostPay Transaction Refund.')->render(),
            __("External Refund Id: %1", $externalRefundId)->render(),
            __("Status: %1", $refundResponseStatus)->render(),
            __("Description: %1", $refundResponseDescription)->render()
        ];

        $creditmemo?->addComment(implode(PHP_EOL, $creditmemoCommentData));
        $creditmemo?->setState($this->getMappedCreditmemoState($refundResponseStatus));
    }

    private function getMappedCreditmemoState(?string $refundResponseStatus): int
    {
        return match ($refundResponseStatus) {
            InPostRefundStatus::PENDING->value => Creditmemo::STATE_OPEN,
            InPostRefundStatus::SUCCESS->value => Creditmemo::STATE_REFUNDED,
            default => Creditmemo::STATE_CANCELED,
        };
    }

    /**
     * @param array $response
     * @return void
     * @throws LocalizedException
     */
    private function validResponse(array $response): void
    {
        if (array_key_exists('error', $response)) {
            // This error is already logged in other place
            throw new LocalizedException(
                __('InPost Pay API refund request responded with error: %1', (string)$response['error'])
            );
        }

        if (empty($response['body'] ?? null) || !$response['body'] instanceof TransactionRefundResponse) {
            $errorMsg = 'Invalid Transaction Refund response.';
            $this->logger->error($errorMsg);

            throw new LocalizedException(__($errorMsg));
        }
    }
}
