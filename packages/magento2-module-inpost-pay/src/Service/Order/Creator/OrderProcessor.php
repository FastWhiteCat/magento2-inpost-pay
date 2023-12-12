<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator;

use InPost\InPostPay\Api\OrderPostProcessingStepInterface;
use InPost\InPostPay\Api\OrderProcessingStepInterface;
use InPost\InPostPay\Api\OrderProcessorInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class OrderProcessor implements OrderProcessorInterface
{
    /**
     * @var OrderProcessingStepInterface[]
     */
    private array $orderProcessingSteps = [];

    /**
     * @var OrderPostProcessingStepInterface[]
     */
    private array $orderPostProcessingSteps = [];

    public function __construct(
        private readonly CartManagementInterface $cartManagement,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PaymentMethodManagementInterface $paymentMethodManagement,
        private readonly LoggerInterface $logger,
        array $orderProcessingSteps,
        array $orderPostProcessingSteps
    ) {
        $this->initOrderProcessingSteps($orderProcessingSteps);
        $this->initOrderPostProcessingSteps($orderPostProcessingSteps);
    }

    /**
     * @param Quote $quote
     * @param OrderInterface $inPostOrder
     * @return Order
     * @throws LocalizedException
     */
    public function execute(Quote $quote, OrderInterface $inPostOrder): Order
    {
        try {
            foreach ($this->orderProcessingSteps as $orderProcessingStep) {
                $orderProcessingStep->process($quote, $inPostOrder);
            }

            $order = $this->createOrderFromQuote($quote);

            foreach ($this->orderPostProcessingSteps as $orderPostProcessingStep) {
                $orderPostProcessingStep->process($order, $inPostOrder);
            }

            $this->logger->info(
                sprintf(
                    'Successfully created InPost Pay Order #%s from Quote ID: %s',
                    $order->getIncrementId(),
                    (is_scalar($quote->getId())) ? (string)$quote->getId() : ''
                )
            );

            return $order;
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    /**
     * @param Quote $quote
     * @return Order
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    private function createOrderFromQuote(Quote $quote): Order
    {
        $cartId = (int)((is_scalar($quote->getId())) ? (int)$quote->getId() : null);
        $paymentMethod = $this->paymentMethodManagement->get($cartId);
        $orderId = $this->cartManagement->placeOrder($cartId, $paymentMethod);
        $order = $this->orderRepository->get((int)$orderId);

        if ($order instanceof Order && $order->getId()) {
            return $order;
        } else {
            throw new LocalizedException(__('Something went wrong while placing order.'));
        }
    }

    /**
     * @param array $orderProcessingSteps
     * @return void
     * @throws LocalizedException
     */
    private function initOrderProcessingSteps(array $orderProcessingSteps): void
    {
        foreach ($orderProcessingSteps as $stepCode => $orderProcessingStep) {
            if ($orderProcessingStep instanceof OrderProcessingStepInterface) {
                $orderProcessingStep->setStepCode($stepCode);
                $this->orderProcessingSteps[] = $orderProcessingStep;
            }
        }

        if (empty($this->orderProcessingSteps)) {
            throw new LocalizedException(__('InPost Pay order processing steps are undefined.'));
        }
    }

    /**
     * @param array $orderPostProcessingSteps
     * @return void
     * @throws LocalizedException
     */
    private function initOrderPostProcessingSteps(array $orderPostProcessingSteps): void
    {
        foreach ($orderPostProcessingSteps as $stepCode => $orderPostProcessingStep) {
            if ($orderPostProcessingStep instanceof OrderPostProcessingStepInterface) {
                $orderPostProcessingStep->setStepCode($stepCode);
                $this->orderPostProcessingSteps[] = $orderPostProcessingStep;
            }
        }

        if (empty($this->orderPostProcessingSteps)) {
            throw new LocalizedException(__('InPost Pay order post processing steps are undefined.'));
        }
    }
}
