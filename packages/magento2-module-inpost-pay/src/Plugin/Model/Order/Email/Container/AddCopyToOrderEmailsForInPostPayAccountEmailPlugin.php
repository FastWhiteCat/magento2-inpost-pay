<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Model\Order\Email\Container;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Registry\Order\Email\Sender\InPostPayOrderEmailSenderRegistry;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface;

class AddCopyToOrderEmailsForInPostPayAccountEmailPlugin
{
    public const ORIGINAL_RESULT = 'original_result';
    public const MODIFIED_RESULT = 'modified_result';

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
        $modifiedResult = is_array($result) ? $result : [];
        $inPostPayOrder = $this->inPostPayOrderEmailSenderRegistry->registry();

        if ($inPostPayOrder === null) {
            return $modifiedResult;
        }

        $order = $this->getOrderByInPostPayOrder($inPostPayOrder);

        if ($order === null) {
            return $modifiedResult;
        }

        $inPostPayAccountEmail = $inPostPayOrder->getInPostPayAccountEmail();
        $inPostDigitalDeliveryEmail = $inPostPayOrder->getDigitalDeliveryEmail();
        $magentoCustomerEmail = null;

        if ($order->getCustomerId() !== null) {
            $magentoCustomerEmail = $this->extractMagentoCustomerAccountEmailFromOrder($order);
        }

        $modifiedResult = $this->prepareNotifyEmails(
            $order,
            $modifiedResult,
            $inPostPayAccountEmail,
            $inPostDigitalDeliveryEmail,
            $magentoCustomerEmail
        );

        $resultObject = new DataObject();
        $resultObject->setData(self::ORIGINAL_RESULT, $originalResult);
        $resultObject->setData(self::MODIFIED_RESULT, $modifiedResult);

        $this->eventManager->dispatch(
            'inpost_pay_order_sales_email_copy_to_before_send',
            [
                'order' => $order,
                'inpost_pay_order' => $inPostPayOrder,
                'result' => $resultObject,
            ]
        );

        $modifiedResult = $resultObject->getData(self::MODIFIED_RESULT);
        $modifiedResult = is_array($modifiedResult) ? $modifiedResult : [];

        if (!empty($modifiedResult) && (array)$originalResult !== $modifiedResult) {
            $this->logger->debug(
                sprintf(
                    'Additional InPost Pay Order [#%s] related email will be sent [as:%s] for %s [originally to: %s]',
                    (string)$order->getIncrementId(),
                    is_scalar($subject->getCopyMethod()) ? (string)$subject->getCopyMethod() : '',
                    implode(',', $modifiedResult),
                    $order->getCustomerEmail()
                )
            );
        }

        return $modifiedResult;
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
        } catch (LocalizedException $e) {
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
        ?string $inPostDigitalDeliveryEmail,
        ?string $magentoCustomerEmail
    ): array {
        $emailsToNotify = [];
        !empty($inPostDigitalDeliveryEmail) && $emailsToNotify[] = $inPostDigitalDeliveryEmail;

        if ($inPostPayAccountEmail) {
            /**
             * In this case InPost Pay account email was sent to Magento.
             * In the next lines of code Magento customer email is added to notify emails ONLY IF it is different
             * from InPost Pay account email because InPost Pay will automatically redirect emails sent to
             * the order's assigned email address. This is done to prevent customer from getting duplicate of the same
             * email message: first from Magento, second redirected from InPost Pay
             */
            if ($magentoCustomerEmail && $inPostPayAccountEmail !== $magentoCustomerEmail) {
                $emailsToNotify[] = $magentoCustomerEmail;
            }

            /**
             * Same logic is applied to digital delivery email
             */
            if ($inPostDigitalDeliveryEmail && $inPostPayAccountEmail !== $inPostDigitalDeliveryEmail) {
                $emailsToNotify[] = $inPostDigitalDeliveryEmail;
            }
        } else {
            if ($magentoCustomerEmail) {
                $emailsToNotify[] = $magentoCustomerEmail;
            }

            if ($inPostDigitalDeliveryEmail) {
                $emailsToNotify[] = $inPostDigitalDeliveryEmail;
            }
        }

        $emailsToNotify = $this->cleanOrderEmailCopyTo($order, $emailsToNotify);

        return array_unique(array_merge($originalNotifyEmails, $emailsToNotify));
    }
}
