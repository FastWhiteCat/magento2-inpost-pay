<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Order;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Service\ApiConnector\UpdateOrder;
use InPost\InPostPay\Service\ApiConnector\Merchant\OrderEvent;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class UpdateInPostOrderEventObserver implements ObserverInterface
{
    public function __construct(
        private readonly UpdateOrder $updateOrder,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getData('order');
        if ($order instanceof Order && $this->canSync($order)) {
            $orderId = is_scalar($order->getId()) ? (int)$order->getId() : null;
            if ($orderId == null) {
                $this->logger->error('Empty order ID. Processing order sync cannot be continued.');
                return;
            }

            try {
                $inPostPayOrder = $this->getInPostPayOrderByOrderId($orderId);
                if ($inPostPayOrder) {
                    $this->updateOrder->execute($order, $inPostPayOrder);
                }
            } catch (LocalizedException $e) {
                $errorMsg = 'Order synchronization with InPost Pay was not successful.';
                $this->logger->error(sprintf('%s Reason: %s', $errorMsg, $e->getMessage()));
            }
        }
    }

    private function canSync(Order $order): bool
    {
        if ($order->getData(OrderEvent::SKIP_INPOST_PAY_SYNC_FLAG)) {
            return false;
        }

        if ($order->getOrigData('status') === $order->getStatus()) {
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
        try {
            $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId($orderId);
        } catch (NoSuchEntityException) {
            $inPostPayOrder = null;
        }

        return $inPostPayOrder;
    }
}
