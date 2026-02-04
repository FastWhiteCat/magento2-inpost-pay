<?php

declare(strict_types=1);

namespace InPost\InPostPay\Cron;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Model\ResourceModel\InPostPayOrder\CollectionFactory as InPostPayOrderCollectionFactory;
use InPost\InPostPay\Model\ResourceModel\InPostPayOrder\Collection as InPostPayOrderCollection;
use InPost\InPostPay\Provider\Config\OrderErrorsHandling\Timeout;
use InPost\InPostPay\Service\ApiConnector\Merchant\OrderEvent;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class HandleTimedOutOrders
{
    /**
     * @param Timeout $config
     * @param InPostPayOrderCollectionFactory $collectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Timeout $config,
        private readonly InPostPayOrderCollectionFactory $collectionFactory,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderManagementInterface $orderManagement,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        foreach ($this->getTimedOutInPostPayOrders() as $inPostPayOrder) {
            /** @var InPostPayOrderInterface $inPostPayOrder */
            $this->handleTimeout($inPostPayOrder);
        }
    }

    /**
     * @param int $orderId
     * @param string $comment
     * @return void
     */
    public function addOrderCommentByOrderId(int $orderId, string $comment): void
    {
        try {
            $order = $this->orderRepository->get($orderId);
            /** @phpstan-ignore-next-line */
            $order->addCommentToStatusHistory(__('InPost Pay Timed Out Order Handling: %1', $comment)->render());
            $order->setData(OrderEvent::SKIP_INPOST_PAY_SYNC_FLAG, true);
            $this->orderRepository->save($order);
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Could not add comment to timed-out order ID:%s. Reason: %s', $orderId, $e->getMessage())
            );
        }
    }

    /**
     * @param InPostPayOrderInterface $inPostPayOrder
     * @return void
     */
    private function handleTimeout(InPostPayOrderInterface $inPostPayOrder): void
    {
        try {
            $orderId = (int)$inPostPayOrder->getOrderId();

            if ($this->config->canCancel()) {

                $this->cancelOrderById($orderId);
            } elseif ($this->config->canChangeStatus()) {
                $status = $this->config->getTimedOutStatus();

                if (empty($status)) {
                    return;
                }

                $this->updateOrderStatus($orderId, $status);
            } else {
                return;
            }

            $inPostPayOrder->setIsTimeOutHandled(true);
            $this->inPostPayOrderRepository->save($inPostPayOrder);
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf(
                    'Error while handling timed-out InPost Pay order ID: %s. Reason: %s',
                    $orderId ?? 'N/A',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @return InPostPayOrderCollection
     */
    private function getTimedOutInPostPayOrders(): InPostPayOrderCollection
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(InPostPayOrderInterface::IS_TIMED_OUT, ['eq' => 1]);
        $collection->addFieldToFilter(InPostPayOrderInterface::IS_TIME_OUT_HANDLED, ['eq' => 0]);

        return $collection;
    }

    /**
     * @param int $orderId
     * @return void
     */
    private function cancelOrderById(int $orderId): void
    {
        try {
            $this->orderManagement->cancel($orderId);
            $comment = 'Order has been canceled because timed out orders cannot be paid by customer.';
            $this->addOrderCommentByOrderId($orderId, __($comment)->render());
            $this->logger->debug(
                sprintf('Order #%d has been canceled because it was timed out while placing.', $orderId)
            );
        } catch (Throwable $e) {
            $this->logger->error(sprintf('Unable to cancel order #%d: %s', $orderId, $e->getMessage()));
        }
    }

    /**
     * @param int $orderId
     * @param string $status
     * @return void
     */
    private function updateOrderStatus(int $orderId, string $status): void
    {
        try {
            $order = $this->orderRepository->get($orderId);
            $order->setStatus($status);
            $comment = 'Order status has changed because it was timed out while placing.';
            $this->addOrderCommentByOrderId($orderId, __($comment)->render());
            $this->logger->debug(
                sprintf('Order #%d status has changed because it was timed out while placing.', $orderId)
            );
        } catch (Throwable $e) {
            $this->logger->error(sprintf('Failed to change status for order #%d: %s', $orderId, $e->getMessage()));
        }
    }
}
