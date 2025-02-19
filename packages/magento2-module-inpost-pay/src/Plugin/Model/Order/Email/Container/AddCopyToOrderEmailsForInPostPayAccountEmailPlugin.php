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
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface;

class AddCopyToOrderEmailsForInPostPayAccountEmailPlugin
{
    /**
     * @param InPostPayOrderEmailSenderRegistry $inPostPayOrderEmailSenderRegistry
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param EventManager $eventManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly InPostPayOrderEmailSenderRegistry $inPostPayOrderEmailSenderRegistry,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly EventManager $eventManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     *  In case order has been placed using InPost Pay Customer internal email address which redirects
     *  incoming messages to email address used by Customer to create Account in InPost Pay App.
     *  However, if an order has been assigned to an existing Magento Account and that Accounts email address
     *  is different from the one used in InPost Pay Account, Copy To is applied so that the messages will reach
     *  both InPost Pay Account email and Magento Account email.
     *
     * @param IdentityInterface $subject
     * @param array|bool $result
     * @return array|bool
     */
    public function afterGetEmailCopyTo(IdentityInterface $subject, array|bool $result): array|bool
    {
        $originalResult = $result;
        $result = is_array($result) ? $result : [];
        $inPostPayOrder = $this->inPostPayOrderEmailSenderRegistry->registry();

        if ($inPostPayOrder === null) {
            return $result;
        }

        $order = $this->getOrderByInPostPayOrder($inPostPayOrder);

        if ($order === null) {
            return $result;
        }

        $inPostPayAccountEmail = $inPostPayOrder->getInPostPayAccountEmail();
        $inPostDeliveryEmail = $inPostPayOrder->getDeliveryEmail();
        $inPostDigitalDeliveryEmail = $inPostPayOrder->getDigitalDeliveryEmail();
        $magentoCustomerEmail = null;

        if ($order->getCustomerId() !== null) {
            $magentoCustomerEmail = $this->extractMagentoCustomerAccountEmailFromOrder($order);
        }

        $result = $this->prepareNotifyEmails(
            $order,
            $result,
            $inPostPayAccountEmail,
            $inPostDeliveryEmail,
            $inPostDigitalDeliveryEmail,
            $magentoCustomerEmail
        );

        $this->eventManager->dispatch(
            'inpost_pay_order_sales_email_copy_to_before_send',
            [
                'order' => $order,
                'inpost_pay_order' => $inPostPayOrder,
                'original_result' => $originalResult,
                'result' => $result,
            ]
        );

        if (!empty($result) && (array)$originalResult !== $result) {
            $this->logger->debug(
                sprintf(
                    'Additional InPost Pay Order [#%s] related email will be sent [as:%s] for %s [originally to: %s]',
                    (string)$order->getIncrementId(),
                    is_scalar($subject->getCopyMethod()) ? (string)$subject->getCopyMethod() : '',
                    implode(',', $result),
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
            /** @var Order $order */
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

    private function cleanOrderEmailCopyTo(Order $order, array $emailsToNotify): array
    {
        $magentoOrderEmail = $order->getCustomerEmail();
        $emailsToNotify = array_unique($emailsToNotify);

        return array_filter($emailsToNotify, function ($item) use ($magentoOrderEmail) {
            return $item !== $magentoOrderEmail;
        });
    }

    private function prepareNotifyEmails(
        Order $order,
        array $originalNotifyEmails,
        ?string $inPostPayAccountEmail,
        ?string $inPostDeliveryEmail,
        ?string $inPostDigitalDeliveryEmail,
        ?string $magentoCustomerEmail
    ): array {
        $emailsToNotify = [];
        !empty($inPostPayAccountEmail) && $emailsToNotify[] = $inPostPayAccountEmail;
        !empty($inPostDeliveryEmail) && $emailsToNotify[] = $inPostDeliveryEmail;
        !empty($inPostDigitalDeliveryEmail) && $emailsToNotify[] = $inPostDigitalDeliveryEmail;
        !empty($magentoCustomerEmail) && $emailsToNotify[] = $magentoCustomerEmail;
        $emailsToNotify = $this->cleanOrderEmailCopyTo($order, $emailsToNotify);

        return array_unique(array_merge($originalNotifyEmails, $emailsToNotify));
    }
}
