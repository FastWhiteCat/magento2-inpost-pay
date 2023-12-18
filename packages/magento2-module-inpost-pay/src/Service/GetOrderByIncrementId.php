<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class GetOrderByIncrementId
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * @param string $incrementId
     * @return OrderInterface
     * @throws NoSuchEntityException
     */
    public function get(string $incrementId): OrderInterface
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::INCREMENT_ID, $incrementId)
            ->create();
        $orders = $this->orderRepository->getList($criteria)->getItems();
        if (count($orders)) {
            if (current($orders) instanceof OrderInterface) {
                $order = current($orders);
            }
        }
        if (!isset($order)) {
            throw new NoSuchEntityException(__('Order #%1 not found.'));
        }
        return $order;
    }
}
