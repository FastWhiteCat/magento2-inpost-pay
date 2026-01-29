<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Refund;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\Merchant\Refund\EventDataInterface;
use InPost\InPostPay\Api\Data\RefundTransactionInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Enum\InPostTransactionStatus;
use InPost\InPostPay\Exception\InPostPayBadRequestException;
use InPost\InPostPay\Exception\OrderNotFoundException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Psr\Log\LoggerInterface;

class RefundEventValidator
{
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param CreditmemoInterface $creditmemo
     * @param EventDataInterface $eventData
     * @return void
     * @throws InPostPayBadRequestException
     * @throws OrderNotFoundException
     */
    public function validate(CreditmemoInterface $creditmemo, EventDataInterface $eventData): void
    {
        $creditmemoId = $creditmemo->getEntityId();
        $creditmemoId = is_scalar($creditmemoId) ? (int)$creditmemoId : 0;
        $orderId = (int)$creditmemo->getOrderId();
        $refundReference = $eventData->getRefundReference();
        $amount = $eventData->getAmount();
        $status = $eventData->getStatus() ?? '';

        if (empty($status)
            || !in_array(
                $status,
                [InPostTransactionStatus::REFUNDED->value, InPostTransactionStatus::DECLINED->value],
                true
            )
        ) {
            $validationError = __('InPost Pay refund webhook error: unknown refund status, skipping.');
            $this->logger->warning(
                $validationError->render(),
                [
                    RefundTransactionInterface::CREDITMEMO_ID => $creditmemoId,
                    InPostPayOrderInterface::ORDER_ID => $orderId,
                    RefundTransactionInterface::REFUND_EXTERNAL_TRANSACTION_ID => $refundReference,
                    'status' => $status
                ]
            );

            throw new InPostPayBadRequestException($validationError);
        }

        try {
            $this->inPostPayOrderRepository->getByOrderId($orderId);
        } catch (NoSuchEntityException $e) {
            $validationError = __('InPost Pay refund webhook error: order is not an InPost Pay order, skipping.');
            $this->logger->warning(
                $validationError->render(),
                [
                    InPostPayOrderInterface::ORDER_ID => $orderId,
                    RefundTransactionInterface::CREDITMEMO_ID => $creditmemoId,
                    RefundTransactionInterface::REFUND_EXTERNAL_TRANSACTION_ID => $refundReference,
                ]
            );

            throw new OrderNotFoundException($validationError);
        }

        $incomingRefundCents = $amount->getValueInCents();
        $creditmemoTotal = (float) $creditmemo->getGrandTotal();
        $creditmemoCents = (int) round($creditmemoTotal * 100);

        if (abs($incomingRefundCents) !== abs($creditmemoCents)) {
            $validationError = __('InPost Pay refund webhook error: amount mismatch, skipping.');
            $this->logger->warning(
                $validationError->render(),
                [
                    RefundTransactionInterface::CREDITMEMO_ID => $creditmemoId,
                    InPostPayOrderInterface::ORDER_ID => $orderId,
                    RefundTransactionInterface::REFUND_EXTERNAL_TRANSACTION_ID => $refundReference,
                    'incoming_value_in_cents' => abs($incomingRefundCents),
                    'creditmemo_grand_total' => $creditmemoTotal,
                    'creditmemo_value_in_cents' => abs($creditmemoCents)
                ]
            );

            throw new InPostPayBadRequestException($validationError);
        }

        $incomingRefundCurrency = strtoupper($amount->getCurrency());
        $creditmemoCurrency = strtoupper((string)$creditmemo->getOrderCurrencyCode());

        if ($incomingRefundCurrency !== $creditmemoCurrency) {
            $validationError = __('InPost Pay refund webhook error: currency mismatch, skipping.');
            $this->logger->warning(
                $validationError->render(),
                [
                    RefundTransactionInterface::CREDITMEMO_ID => $creditmemoId,
                    InPostPayOrderInterface::ORDER_ID => $orderId,
                    RefundTransactionInterface::REFUND_EXTERNAL_TRANSACTION_ID => $refundReference,
                    'incoming_currency' => $incomingRefundCurrency,
                    'creditmemo_currency' => $creditmemoTotal
                ]
            );

            throw new InPostPayBadRequestException($validationError);
        }
    }
}
