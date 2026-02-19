<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Adminhtml\Order\View;

use Exception;
use InPost\InPostPay\Model\Registry\CurrentOrderRegistry;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\View;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class RegisterCurrentOrderPlugin
{
    public function __construct(
        private readonly CurrentOrderRegistry $currentOrderRegistry,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function beforeExecute(View $subject): void
    {
        $orderId = $subject->getRequest()->getParam('order_id');
        try {
            $orderId = (is_scalar($orderId)) ? (int)$orderId : 0;
            $order = $this->orderRepository->get($orderId);
            if ($order instanceof Order) {
                $this->currentOrderRegistry->setOrder($order);
            }
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'Could not register currently viewed order (ID:%1). Reason: %2',
                    $orderId,
                    $e->getMessage()
                )->render()
            );
        }
    }
}
