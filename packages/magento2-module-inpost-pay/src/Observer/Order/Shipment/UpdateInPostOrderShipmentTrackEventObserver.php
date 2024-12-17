<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Order\Shipment;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Service\ApiConnector\UpdateOrder;
use InPost\InPostPay\Service\ApiConnector\Merchant\OrderEvent;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class UpdateInPostOrderShipmentTrackEventObserver implements ObserverInterface
{
    private array $inPostPayOrders = [];

    public function __construct(
        private readonly UpdateOrder $updateOrder,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $shipmentTrack = $observer->getEvent()->getData('track');

        if ($shipmentTrack instanceof ShipmentTrackInterface) {
            try {
                $orderId = (int)$shipmentTrack->getOrderId();
                $order = $this->getOrderById($orderId);
                $inPostPayOrder = $this->getInPostPayOrderByOrderId($orderId);

                if ($inPostPayOrder && $this->canSync($order)) {
                    $this->updateOrder->execute($order, $inPostPayOrder);
                }
            } catch (LocalizedException $e) {
                $errorMsg = 'Order synchronization with InPost Pay was not successful.';
                $this->logger->error(sprintf('%s Reason: %s', $errorMsg, $e->getMessage()));
            }
        }
    }

    /**
     * @param int $orderId
     * @return Order
     * @throws NoSuchEntityException
     */
    private function getOrderById(int $orderId): Order
    {
        $order = $this->orderRepository->get($orderId);

        if ($order instanceof Order && !empty($order->getId())) {
            return $order;
        }

        throw new NoSuchEntityException(__('Order with ID: %1 does not exist.', $orderId));
    }

    private function canSync(Order $order): bool
    {
        if ($order->getData(OrderEvent::SKIP_INPOST_PAY_SYNC_FLAG)) {
            return false;
        }

        $orderId = (int)(is_scalar($order->getId()) ? $order->getId() : null);
        $inPostPayOrder = $this->getInPostPayOrderByOrderId($orderId);

        if (!$inPostPayOrder) {
            return false;
        }

        return true;
    }

    private function getInPostPayOrderByOrderId(int $orderId): ?InPostPayOrderInterface
    {
        if (!isset($this->inPostPayOrders[$orderId])) {
            try {
                $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId($orderId);
            } catch (NoSuchEntityException) {
                $inPostPayOrder = null;
            }

            $this->inPostPayOrders[$orderId] = $inPostPayOrder;
        }

        return $this->inPostPayOrders[$orderId];
    }
}
