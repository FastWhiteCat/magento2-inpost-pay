<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Order\Email\Sender;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Registry\Order\Email\Sender\InPostPayOrderEmailSenderRegistry;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;

class InPostPayOrderEmailSenderObserver implements ObserverInterface
{
    /**
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     * @param InPostPayOrderEmailSenderRegistry $inPostPayOrderEmailSenderRegistry
     */
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly InPostPayOrderEmailSenderRegistry $inPostPayOrderEmailSenderRegistry
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $transportObject = $observer->getEvent()->getData('transportObject');

        if (!$transportObject instanceof DataObject) {
            return;
        }

        $order = $transportObject->getData('order');

        if (!$order instanceof Order) {
            return;
        }

        $inPostPayOrder = $this->getInPostPayOrder($order);

        if ($inPostPayOrder === null) {
            return;
        }

        $this->inPostPayOrderEmailSenderRegistry->register($inPostPayOrder);
    }

    private function getInPostPayOrder(Order $order): ?InPostPayOrderInterface
    {
        $orderId = is_scalar($order->getId()) ? (int)$order->getId() : null;

        try {
            if ($orderId) {
                return $this->inPostPayOrderRepository->getByOrderId($orderId);
            }

            return $this->inPostPayOrderRepository->getByOrderId((int)$orderId);
        } catch (NoSuchEntityException $e) {
            $inPostPayOrder = $this->inPostPayOrderEmailSenderRegistry->registry();

            if ($inPostPayOrder && $orderId) {
                $inPostPayOrder->setOrderId($orderId);

                return $inPostPayOrder;
            }

            return null;
        }
    }
}
