<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Model\Order\Email\Container;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Registry\Order\Email\Sender\InPostPayOrderEmailSenderRegistry;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Psr\Log\LoggerInterface;

class AddCopyToOrderEmailsForInPostPayAccountEmailPlugin
{
    /**
     * @param InPostPayOrderEmailSenderRegistry $inPostPayOrderEmailSenderRegistry
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly InPostPayOrderEmailSenderRegistry $inPostPayOrderEmailSenderRegistry,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly LoggerInterface $logger
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

        if ($order === null || $order->getCustomerId() === null) {
            return $result;
        }

        $customerEmail = $this->extractMagentoCustomerAccountEmailFromOrder($order);
        $inPostPayAccountEmail = $inPostPayOrder->getInPostPayAccountEmail();

        if ($customerEmail && $customerEmail !== $inPostPayAccountEmail) {
            /**
             * In this case order has been placed using InPost Pay Customer internal email address which redirects
             * incoming messages to email address used by Customer to create Account in InPost Pay App.
             * However, if an order has been assigned to an existing Magento Account and that Accounts email address
             * is different from the one used in InPost Pay Account, Copy To is applied so that the messages will reach
             * both InPost Pay Account email and Magento Account email.
             */
            $result = is_array($result) ? $result : [];
            $result[] = $customerEmail;
            $this->logger->debug(
                sprintf(
                    'Additional InPost Pay order related email will be send [mode:%s] for %s [in addition to: %s]',
                    is_scalar($subject->getCopyMethod()) ? (string)$subject->getCopyMethod() : '',
                    $customerEmail,
                    $order->getCustomerEmail()
                )
            );
        }

        return $result;
    }

    /**
     * @param InPostPayOrderInterface $inPostPayOrder
     * @return Order|null
     */
    private function getOrderByInPostPayOrder(InPostPayOrderInterface $inPostPayOrder): ?Order
    {
        try {
            $order = $this->orderRepository->get($inPostPayOrder->getOrderId());
        } catch (LocalizedException $e) {
            $order = null;
        }

        return $order;
    }

    private function extractMagentoCustomerAccountEmailFromOrder(Order $order): ?string
    {
        $customerEmail = null;

        try {
            $customerId = (int)$order->getCustomerId();
            $customer = $order->getCustomer() ?? $this->customerRepository->getById($customerId);

            if ($customer instanceof CustomerInterface || $customer instanceof Customer) {
                $customerEmail = $customer->getEmail();
            }
        } catch (NoSuchEntityException | LocalizedException $e) {
            return null;
        }

        return $customerEmail;
    }
}
