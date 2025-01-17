<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class GetOrderById
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @param int $orderId
     * @return OrderInterface
     * @throws NoSuchEntityException
     */
    public function get(int $orderId): OrderInterface
    {
        try {
            return $this->orderRepository->get($orderId);
        } catch (Exception $e) {
            throw new NoSuchEntityException(__('Order ID:%1 not found.'));
        }
    }
}
