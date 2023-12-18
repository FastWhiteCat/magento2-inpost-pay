<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Updater\Steps;

use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Api\OrderPostProcessingStepInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface as InPostOrderInterface;
use InPost\InPostPay\Service\Order\Creator\Steps\OrderProcessingStep;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class SaveAcceptedConsentsStep extends OrderProcessingStep implements OrderPostProcessingStepInterface
{
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    public function process(Order $order, InPostOrderInterface $inPostOrder): void
    {
        $orderId = (int)(is_scalar($order->getEntityId()) ? $order->getEntityId() : null);
        $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId($orderId);
        $inPostPayOrder->setAcceptedConsents($inPostOrder->getConsents());
        $this->inPostPayOrderRepository->save($inPostPayOrder);

        $this->createLog(
            sprintf('InPost Pay Order accepted consents were applied on Order #%s', (string)$order->getIncrementId()),
            $inPostPayOrder->getDeliveryOptions()
        );
    }
}
