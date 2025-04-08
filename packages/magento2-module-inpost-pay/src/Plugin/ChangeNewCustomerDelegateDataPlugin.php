<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin;

use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderCustomerDelegateInterface;
use Magento\Sales\Model\Order\OrderCustomerExtractor;
use Magento\Customer\Api\AccountDelegationInterface;

class ChangeNewCustomerDelegateDataPlugin
{
    /**
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     * @param OrderCustomerExtractor $customerExtractor
     * @param AccountDelegationInterface $delegateService
     */
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly OrderCustomerExtractor $customerExtractor,
        private readonly AccountDelegationInterface $delegateService
    ) {
    }

    /**
     * Handles the delegation process for a new customer after the account is created.
     * Changes delegated for registration form email address from internal InPost Pay customer's account delivery email
     * into original email address used by customer to create InPost Pay Account.
     *
     * @param OrderCustomerDelegateInterface $subject The subject instance invoking the method.
     * @param Redirect $result The initial redirect result.
     * @param int $orderId The ID of the order being processed.
     * @return Redirect The updated redirect instance.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelegateNew(OrderCustomerDelegateInterface $subject, Redirect $result, int $orderId): Redirect
    {
        try {
            $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId($orderId);
            $inPostAccountEmail = $inPostPayOrder->getInPostPayAccountEmail();

            if ($inPostAccountEmail === null) {
                return $result;
            }

            $newCustomer = $this->customerExtractor->extract($orderId);
            $newCustomer->setEmail($inPostAccountEmail);

            return $this->delegateService->createRedirectForNew(
                $newCustomer,
                ['__sales_assign_order_id' => $orderId]
            );
        } catch (NoSuchEntityException $e) {
            return $result;
        }
    }
}
