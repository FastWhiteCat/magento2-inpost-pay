<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Checkout;

use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Service\Order\Creator\Steps\PaymentMethodStep;
use InPost\InPostPay\Traits\AnonymizerTrait;
use Magento\Checkout\Block\Registration;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class ReplaceOrderDeliveryEmailWithAccountEmailPlugin
{
    use AnonymizerTrait;

    /**
     * @param Session $checkoutSession
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Session $checkoutSession,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Registration $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetEmailAddress(Registration $subject, string $result): string
    {
        $lastRealOrder = $this->checkoutSession->getLastRealOrder();
        $lastRealOrderId = $lastRealOrder->getEntityId();
        $paymentMethod = $lastRealOrder->getPayment() ? $lastRealOrder->getPayment()->getMethod() : '';

        if (!is_scalar($lastRealOrderId)
            || $paymentMethod !== PaymentMethodStep::INPOST_PAY_PAYMENT_METHOD_CODE
        ) {
            return $result;
        }

        try {
            $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId((int)$lastRealOrderId);
            $inPostPayAccountEmail = $inPostPayOrder->getInPostPayAccountEmail();

            $this->logger->debug(
                'InPost Pay Order found. Replacing order email with InPost Pay account email address.',
                [
                    'last_real_order_id' => (int)$lastRealOrderId,
                    'payment_method' => $paymentMethod,
                    'inpostpay_account_email' => $this->anonymizeEmail($inPostPayAccountEmail ?? '')
                ]
            );
        } catch (NoSuchEntityException $e) {
            $this->logger->error(
                'InPost Pay Order not found. Cannot replace order email with InPost Pay account email address.',
                [
                    'last_real_order_id' => (int)$lastRealOrderId,
                    'payment_method' => $paymentMethod
                ]
            );

            $inPostPayAccountEmail = null;
        }

        return $inPostPayAccountEmail ?? $result;
    }
}
