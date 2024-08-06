<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\InPostPayLockerIdProviderInterface;
use InPost\InPostPay\Api\OrderLockerServiceInterface;
use InPost\InPostPay\Model\InPostPayOrderRepository;
use InPost\InPostPay\Provider\InPostDeliveryModuleProvider;
use InPost\InPostPay\Service\ApiConnector\Merchant\OrderEvent;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class OrderLockerService implements OrderLockerServiceInterface
{
    public function __construct(
        private readonly InPostPayOrderRepository $inPostPayOrderRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly InPostDeliveryModuleProvider $inPostDeliveryModuleProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    public function setLockerIdForOrder(Order $order, string $lockerId): void
    {
        try {
            $orderId = is_scalar($order->getId()) ? (int)$order->getId() : 0;
            $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId($orderId);
            $this->setLockerIdOnInPostOrder($inPostPayOrder, $lockerId);
            if ($this->inPostDeliveryModuleProvider->isEnabled()) {
                $this->setLockerIdOnMagentoOrder($order, $lockerId);
            }
        } catch (LocalizedException $e) {
            $errorPhrase = __(
                'Could not assign Locker "%1" to Order #%2. Reason: %3',
                $lockerId,
                (string)$order->getIncrementId(),
                $e->getMessage()
            );
            $this->logger->error($errorPhrase->render());

            throw new LocalizedException($errorPhrase);
        }
    }

    /**
     * Saving inpost_locker_id value on Order is required for Smartmage_Inpost order handling
     *
     * @param Order $order
     * @param string $lockerId
     * @return void
     */
    private function setLockerIdOnMagentoOrder(Order $order, string $lockerId): void
    {
        $order->setData(InPostPayLockerIdProviderInterface::INPOST_LOCKER_ID_FIELD, $lockerId);
        $order->setData(OrderEvent::SKIP_INPOST_PAY_SYNC_FLAG, true);
        $this->orderRepository->save($order);
        $this->logger->debug(
            sprintf(
                'Locker ID %s was saved in Magento Order ID %s ',
                $lockerId,
                is_scalar($order->getId()) ? (int)$order->getId() : ''
            )
        );
    }

    /**
     * @param InPostPayOrderInterface $inPostPayOrder
     * @param string $lockerId
     * @return void
     * @throws LocalizedException
     */
    private function setLockerIdOnInPostOrder(InPostPayOrderInterface $inPostPayOrder, string $lockerId): void
    {
        $inPostPayOrder->setLockerId($lockerId);
        $this->inPostPayOrderRepository->save($inPostPayOrder);
        $this->logger->debug(
            sprintf('Locker ID %s was saved in InPost Pay Order ID %s ', $lockerId, $inPostPayOrder->getOrderId())
        );
    }
}
