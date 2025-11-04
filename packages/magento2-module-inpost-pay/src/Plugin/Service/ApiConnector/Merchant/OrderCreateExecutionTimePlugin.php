<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderCreateInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\InvoiceDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Cron\HandleTimedOutOrders;
use InPost\InPostPay\Provider\Config\OrderErrorsHandling\Timeout;
use Psr\Log\LoggerInterface;
use Throwable;

class OrderCreateExecutionTimePlugin
{
    /**
     * Keeps start times per subject instance.
     * @var array<string,float>
     */
    private array $startTimes = [];

    /**
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     * @param HandleTimedOutOrders $handleTimedOutOrders
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly HandleTimedOutOrders $handleTimedOutOrders,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param OrderCreateInterface $subject
     * @param OrderDetailsInterface $orderDetails
     * @param AccountInfoInterface $accountInfo
     * @param DeliveryInterface $delivery
     * @param array $consents
     * @param InvoiceDetailsInterface|null $invoiceDetails
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(
        OrderCreateInterface $subject,
        OrderDetailsInterface $orderDetails,
        AccountInfoInterface $accountInfo,
        DeliveryInterface $delivery,
        array $consents,
        ?InvoiceDetailsInterface $invoiceDetails = null
    ): ?array {
        $basketId = $orderDetails->getBasketId();
        $this->startTimes[$basketId] = microtime(true);

        return null;
    }

    /**
     * @param OrderCreateInterface $subject
     * @param OrderInterface $result
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(OrderCreateInterface $subject, OrderInterface $result): OrderInterface
    {
        $basketId = $result->getOrderDetails()->getBasketId();
        $start = $this->startTimes[$basketId] ?? null;

        if ($start === null) {
            return $result;
        }

        $duration = microtime(true) - $start;
        unset($this->startTimes[$basketId]);

        $this->logger->info(sprintf('OrderCreateInterface::execute took %.2f seconds', $duration));

        if ($duration < Timeout::TIMEOUT_THRESHOLD_SECONDS) {
            return $result;
        }

        try {
            $orderDetails = $result->getOrderDetails();
            $orderIdStr = $orderDetails->getOrderId();
            $orderId = is_numeric($orderIdStr) ? (int)$orderIdStr : 0;
            $entity = $this->inPostPayOrderRepository->getByOrderId($orderId, true);
            $entity->setIsTimedOut(true);
            $this->inPostPayOrderRepository->save($entity);
            $this->logger->warning(
                sprintf(
                    'Marked InPost Pay order as timed out (order_id=%d, duration=%.2fs)',
                    $orderId,
                    $duration
                )
            );
            $this->handleTimedOutOrders->addOrderCommentByOrderId(
                $orderId,
                __('Order has been placed but process took %1s.', round($duration, 2))->render()
                . ' '
                . __('Limit for order placing execution time is %1s.', Timeout::TIMEOUT_THRESHOLD_SECONDS)->render()
                . ' '
                . __('Customer will not be able to complete and pay for that order in Mobile App.')->render()
            );
        } catch (Throwable $e) {
            $this->logger->error('Failed to mark InPost Pay order as timed out: ' . $e->getMessage());
        }

        return $result;
    }
}
