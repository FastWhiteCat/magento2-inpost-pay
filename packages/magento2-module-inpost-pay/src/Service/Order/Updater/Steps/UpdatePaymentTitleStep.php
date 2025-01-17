<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Updater\Steps;

use InPost\InPostPay\Api\OrderPostProcessingStepInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface as InPostOrderInterface;
use InPost\InPostPay\Model\Config\Payment\TitleUpdater;
use InPost\InPostPay\Service\Order\Creator\Steps\OrderProcessingStep;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class UpdatePaymentTitleStep extends OrderProcessingStep implements OrderPostProcessingStepInterface
{
    public function __construct(
        protected readonly TitleUpdater $titleUpdater,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    public function process(Order $order, InPostOrderInterface $inPostOrder): void
    {
        $paymentType = $inPostOrder->getOrderDetails()->getPaymentType();
        /** @var \Magento\Sales\Api\Data\OrderPaymentInterface $payment */
        $payment = $order->getPayment();
        if (!($payment instanceof \Magento\Sales\Api\Data\OrderPaymentInterface)) {
            return;
        }

        $this->titleUpdater->updatePaymentTitleByType($payment, $order, $paymentType);
    }
}
