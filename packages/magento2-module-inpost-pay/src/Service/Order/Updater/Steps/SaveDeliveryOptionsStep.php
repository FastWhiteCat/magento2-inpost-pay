<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Updater\Steps;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Api\OrderPostProcessingStepInterface;
use InPost\InPostPay\Model\Dto\Order as OrderDto;
use InPost\InPostPay\Service\Order\Creator\Steps\OrderProcessingStep;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class SaveDeliveryOptionsStep extends OrderProcessingStep implements OrderPostProcessingStepInterface
{
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    public function process(Order $order, OrderDto $orderDto): void
    {
        $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId((int)$order->getEntityId());
        $inPostPayOrder->setDeliveryOptions($orderDto->getDelivery()->getDeliveryCodes());
        $this->inPostPayOrderRepository->save($inPostPayOrder);

        $this->createLog(
            sprintf('InPost Pay Order delivery options were applied on Order #%s', (string)$order->getIncrementId()),
            $inPostPayOrder->getDeliveryOptions()
        );
    }
}
