<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use Exception;
use InPost\InPostPay\Api\ApiConnector\Merchant\RefundEventInterface;
use InPost\InPostPay\Api\Data\Merchant\Refund\EventDataInterface;
use InPost\InPostPay\Api\Data\RefundTransactionInterface;
use InPost\InPostPay\Api\RefundTransactionRepositoryInterface;
use InPost\InPostPay\Enum\InPostTransactionStatus;
use InPost\InPostPay\Exception\InPostPayBadRequestException;
use InPost\InPostPay\Exception\OrderNotFoundException;
use InPost\InPostPay\Exception\RefundNotFoundException;
use InPost\InPostPay\Validator\Refund\RefundEventValidator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RefundEvent implements RefundEventInterface
{
    public function __construct(
        private readonly RefundTransactionRepositoryInterface $refundTransactionRepository,
        private readonly CreditmemoRepositoryInterface $creditmemoRepository,
        private readonly RefundEventValidator $refundEventValidator,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param string $eventType
     * @param EventDataInterface $eventData
     * @return bool
     * @throws InPostPayBadRequestException
     * @throws OrderNotFoundException
     * @throws RefundNotFoundException
     */
    public function execute(string $eventType, EventDataInterface $eventData): bool
    {
        $refundReference = $eventData->getRefundReference();

        if (!$refundReference) {
            $validationError = __('InPost Pay refund webhook error: empty refundReference, skipping.');
            $this->logger->warning($validationError->render());

            throw new InPostPayBadRequestException($validationError);
        }

        try {
            $mapping = $this->refundTransactionRepository->getByExternalTransactionId($refundReference);
            $creditmemoId = $mapping->getCreditmemoId();
        } catch (NoSuchEntityException $e) {
            $validationError = __('InPost Pay refund webhook error: creditmemo not found for that refundReference.');
            $this->logger->warning(
                $validationError->render(),
                [RefundTransactionInterface::REFUND_EXTERNAL_TRANSACTION_ID => $refundReference]
            );

            throw new RefundNotFoundException($validationError);
        }

        try {
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
        } catch (NoSuchEntityException $e) {
            $validationError = __('InPost Pay refund webhook error: creditmemo not found by ID.');
            $this->logger->warning(
                $validationError->render(),
                [RefundTransactionInterface::CREDITMEMO_ID => $creditmemoId]
            );

            throw new RefundNotFoundException($validationError);
        }

        $this->refundEventValidator->validate($creditmemo, $eventData);
        $newState = $this->mapRefundStatusToCreditmemoState((string)$eventData->getStatus());

        if ($creditmemo->getState() !== $newState) {
            $creditmemo->setState($newState);
        }

        // @phpstan-ignore-next-line
        $creditmemo->addComment(
            sprintf(
                "InPost Pay refund webhook processed. Status: %s. External Transaction ID: %s. Amount: %s %s",
                (string)$eventData->getStatus(),
                $refundReference,
                number_format((float) $creditmemo->getGrandTotal(), 2, '.', ''),
                $creditmemo->getOrderCurrencyCode()
            )
        );

        $this->creditmemoRepository->save($creditmemo);
        $this->logger->debug(
            'InPost Pay refund webhook processed.',
            [
                RefundTransactionInterface::CREDITMEMO_ID => $creditmemo->getEntityId(),
                RefundTransactionInterface::REFUND_EXTERNAL_TRANSACTION_ID => $refundReference,
                EventDataInterface::STATUS => (string)$eventData->getStatus(),
                CreditmemoInterface::STATE => $creditmemo->getState(),
                'eventType' => $eventType,
            ]
        );

        $this->addOrderCommentForCreditmemo($creditmemo, $refundReference);

        return true;
    }

    /**
     * @param string $status
     * @return int
     */
    private function mapRefundStatusToCreditmemoState(string $status): int
    {
        return match (strtoupper($status)) {
            InPostTransactionStatus::REFUNDED->value => Creditmemo::STATE_REFUNDED,
            InPostTransactionStatus::DECLINED->value => Creditmemo::STATE_CANCELED,
            default => Creditmemo::STATE_OPEN,
        };
    }

    private function addOrderCommentForCreditmemo(CreditmemoInterface $creditmemo, string $externalTransactionId): void
    {
        $orderId = $creditmemo->getOrderId();
        try {
            $order = $this->orderRepository->get($orderId);

            if ($creditmemo->getState() === Creditmemo::STATE_REFUNDED) {
                /** @phpstan-ignore-next-line */
                $order->addCommentToStatusHistory(
                    __(
                        'InPost Pay has confirmed the refund amount:%1 %2 for creditmemo ID:%3. External TXN: %4',
                        (float) $creditmemo->getGrandTotal(),
                        (string) $creditmemo->getOrderCurrencyCode(),
                        (int) $creditmemo->getEntityId(),
                        $externalTransactionId
                    )->render()
                );
            } elseif ($creditmemo->getState() === Creditmemo::STATE_CANCELED) {
                /** @phpstan-ignore-next-line */
                $order->addCommentToStatusHistory(
                    __(
                        'InPost Pay has declined the refund amount:%1 %2 for creditmemo ID:%3. External TXN: %4',
                        (float) $creditmemo->getGrandTotal(),
                        (string) $creditmemo->getOrderCurrencyCode(),
                        (int) $creditmemo->getEntityId(),
                        $externalTransactionId
                    )->render()
                );
            }

            $order->setData(OrderEvent::SKIP_INPOST_PAY_SYNC_FLAG, true);
            $this->orderRepository->save($order);
        } catch (Exception $e) {
            $this->logger->error(
                sprintf(
                    'Could not add InPost Pay Refund comment to order ID:%s. Reason: %s',
                    $orderId,
                    $e->getMessage()
                )
            );
        }
    }
}
