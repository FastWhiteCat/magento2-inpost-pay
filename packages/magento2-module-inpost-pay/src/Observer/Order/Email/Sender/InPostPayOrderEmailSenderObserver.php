<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Order\Email\Sender;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Registry\Order\Email\Sender\InPostPayOrderEmailSenderRegistry;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class InPostPayOrderEmailSenderObserver implements ObserverInterface
{
    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     * @param InPostPayOrderEmailSenderRegistry $inPostPayOrderEmailSenderRegistry
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
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
        try {
            $orderId = is_scalar($order->getId()) ? (int)$order->getId() : null;

            if ($orderId) {
                return $this->inPostPayOrderRepository->getByOrderId($orderId);
            } else {
                $orderId = $this->getOrderIdByIncrementId((string)$order->getIncrementId());
            }

            return $this->inPostPayOrderRepository->getByOrderId((int)$orderId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @param string $incrementId
     * @return Order|null
     */
    private function getOrderIdByIncrementId(string $incrementId): ?int
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $criteria = $searchCriteriaBuilder
            ->addFilter(OrderInterface::INCREMENT_ID, $incrementId)
            ->create();
        $orders = $this->orderRepository->getList($criteria)->getItems();
        $order = count($orders) ? $orders[0] : null;

        return ($order && is_scalar($order->getId())) ? (int)$order->getId() : null;
    }
}
