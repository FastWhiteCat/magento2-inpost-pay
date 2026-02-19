<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Order;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Service\ApiConnector\UpdateOrder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Throwable;

class ForceStatusUpdateObserver implements ObserverInterface
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
        if ($order instanceof Order) {
            $orderId = is_scalar($order->getId()) ? (int)$order->getId() : 0;
            try {
                $inPostPayOrder = $this->getInPostPayOrderByOrderId($orderId);
                if ($inPostPayOrder) {
                    $this->updateOrder->execute($order, $inPostPayOrder);
                    $this->logger->debug(
                        sprintf(
                            'Force InPost Pay Order Status update successfully executed for Order ID: %s',
                            $orderId,
                        )
                    );
                }
            } catch (Throwable $e) {
                $this->logger->error(
                    sprintf(
                        'Force InPost Pay Order Status update failed. Order ID: %s Reason: %s',
                        $orderId,
                        $e->getMessage()
                    )
                );
            }
        }
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
