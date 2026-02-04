<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Updater\Steps;

use InPost\InPostPay\Api\Data\InPostPayOrderInterfaceFactory;
use InPost\InPostPay\Api\OrderLockerServiceInterface;
use InPost\InPostPay\Api\OrderPostProcessingStepInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface as InPostOrderInterface;
use InPost\InPostPay\Enum\InPostDeliveryType;
use InPost\InPostPay\Service\Order\Creator\Steps\OrderProcessingStep;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class SavePickupPointStep extends OrderProcessingStep implements OrderPostProcessingStepInterface
{
    public function __construct(
        private readonly OrderLockerServiceInterface $orderLockerService,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    public function process(Order $order, InPostOrderInterface $inPostOrder): void
    {
        if ($inPostOrder->getDelivery()->getDeliveryType() === InPostDeliveryType::APM->name) {
            $lockerId = (string)$inPostOrder->getDelivery()->getDeliveryPoint();
            $this->orderLockerService->setLockerIdForOrder($order, $lockerId);

            $this->createLog(
                sprintf('Locker ID: %s has been saved for Order #%s', $lockerId, (string)$order->getIncrementId())
            );
        }
    }
}
