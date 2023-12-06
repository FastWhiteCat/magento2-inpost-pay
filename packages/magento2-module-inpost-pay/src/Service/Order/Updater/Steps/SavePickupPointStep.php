<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Updater\Steps;

use InPost\InPostPay\Api\ApiConnector\IziApi\Basket\BasketFieldInterface;
use InPost\InPostPay\Api\Data\InPostPayOrderInterfaceFactory;
use InPost\InPostPay\Api\OrderLockerServiceInterface;
use InPost\InPostPay\Api\OrderPostProcessingStepInterface;
use InPost\InPostPay\Model\Dto\Order as OrderDto;
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

    public function process(Order $order, OrderDto $orderDto): void
    {
        if ($orderDto->getDelivery()->getDeliveryType() === BasketFieldInterface::DELIVERY_TYPE_PICKUP) {
            $lockerId = (string)$orderDto->getDelivery()->getDeliveryPoint();
            $this->orderLockerService->setLockerIdForOrder($order, $lockerId);

            $this->createLog(
                sprintf('Locker ID: %s has been saved for Order #%s', $lockerId, (string)$order->getIncrementId())
            );
        }
    }
}
