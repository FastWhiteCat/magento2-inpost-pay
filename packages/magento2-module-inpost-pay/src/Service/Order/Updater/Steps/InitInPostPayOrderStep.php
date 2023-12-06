<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Updater\Steps;

use InPost\InPostPay\Api\Data\InPostPayOrderInterfaceFactory;
use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Api\OrderPostProcessingStepInterface;
use InPost\InPostPay\Model\Dto\Order as OrderDto;
use InPost\InPostPay\Service\Order\Creator\Steps\OrderProcessingStep;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class InitInPostPayOrderStep extends OrderProcessingStep implements OrderPostProcessingStepInterface
{
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly InPostPayOrderInterfaceFactory $inPostPayOrderFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    public function process(Order $order, OrderDto $orderDto): void
    {
        /** @var InPostPayOrderInterface $inPostPayOrder */
        $inPostPayOrder = $this->inPostPayOrderFactory->create();
        $inPostPayOrder->setOrderId((int)$order->getEntityId());
        $this->inPostPayOrderRepository->save($inPostPayOrder);

        $this->createLog(
            sprintf('InPost Pay Order entity has been initialized for order #%s', (string)$order->getIncrementId())
        );
    }
}
