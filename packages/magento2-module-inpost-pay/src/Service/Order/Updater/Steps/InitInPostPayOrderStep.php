<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Updater\Steps;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\InPostPayOrderInterfaceFactory;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Api\OrderPostProcessingStepInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface as InPostOrderInterface;
use InPost\InPostPay\Service\Order\Creator\Steps\OrderProcessingStep;
use Magento\Framework\Exception\CouldNotSaveException;
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

    /**
     * @param Order $order
     * @param InPostOrderInterface $inPostOrder
     * @return void
     * @throws CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(Order $order, InPostOrderInterface $inPostOrder): void
    {
        /** @var InPostPayOrderInterface $inPostPayOrder */
        $inPostPayOrder = $this->inPostPayOrderFactory->create();
        $orderId = (int)(is_scalar($order->getEntityId()) ? $order->getEntityId() : null);
        $inPostPayOrder->setOrderId($orderId);
        $this->inPostPayOrderRepository->save($inPostPayOrder);

        $this->createLog(
            sprintf('InPost Pay Order entity has been initialized for order #%s', (string)$order->getIncrementId())
        );
    }
}
