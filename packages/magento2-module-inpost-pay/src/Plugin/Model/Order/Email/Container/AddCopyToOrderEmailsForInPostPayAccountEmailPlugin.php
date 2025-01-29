<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Model\Order\Email\Container;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Registry\Order\Email\Sender\InPostPayOrderEmailSenderRegistry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;

class AddCopyToOrderEmailsForInPostPayAccountEmailPlugin
{
    /**
     * @param InPostPayOrderEmailSenderRegistry $inPostPayOrderEmailSenderRegistry
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        private readonly InPostPayOrderEmailSenderRegistry $inPostPayOrderEmailSenderRegistry,
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @param OrderIdentity $subject
     * @param array|bool $result
     * @return array|bool
     */
    public function afterGetEmailCopyTo(OrderIdentity $subject, array|bool $result): array|bool
    {
        $inPostPayOrder = $this->inPostPayOrderEmailSenderRegistry->registry();

        if ($inPostPayOrder === null) {
            return $result;
        }

        $order = $this->getOrderByInPostPayOrder($inPostPayOrder);

        if ($order === null || $order->getCustomer() === null) {
            return $result;
        }

        $customer = $order->getCustomer();
        $customerEmail = $customer->getEmail();
        $inPostPayAccountEmail = $inPostPayOrder->getInPostPayAccountEmail();

        if ($customerEmail !== $inPostPayAccountEmail) {
            /**
             * In this case order has been placed using InPost Pay Customer internal email address which redirects
             * incoming messages to email address used by Customer to create Account in InPost Pay App.
             * However, if an order has been assigned to an existing Magento Account and that Accounts email address
             * is different from the one used in InPost Pay Account, Copy To is applied so that the messages will reach
             * both InPost Pay Account email and Magento Account email.
             */
            $result = is_array($result) ? $result : [];
            $result[] = $customerEmail;
        }

        return $result;
    }

    /**
     * @param InPostPayOrderInterface $inPostPayOrder
     * @return Order|null
     */
    public function getOrderByInPostPayOrder(InPostPayOrderInterface $inPostPayOrder): ?Order
    {
        try {
            $order = $this->orderRepository->get($inPostPayOrder->getOrderId());
        } catch (LocalizedException $e) {
            $order = null;
        }

        return $order;
    }
}
