<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use InPost\InPostPay\Api\InPostPayLockerIdProviderInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use InPost\InPostPay\Service\GetOrderByIncrementId;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class InPostPayLockerIdProvider implements InPostPayLockerIdProviderInterface
{
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly GetOrderByIncrementId $getOrderByIncrementId,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    public function getFromOrderById(int $orderId): string
    {
        try {
            return $this->getFromOrder($this->orderRepository->get($orderId));
        } catch (NoSuchEntityException $e) {
            $this->logger->error(
                __('InPost Locker ID cannot be obtained because order does not exist: %1', $e->getMessage())->render()
            );

            throw $e;
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    public function getFromOrderByIncrementId(string $orderIncrementId): string
    {
        try {
            return $this->getFromOrder($this->getOrderByIncrementId->get($orderIncrementId));
        } catch (NoSuchEntityException $e) {
            $this->logger->error(
                __('InPost Locker ID cannot be obtained because order does not exist: %1', $e->getMessage())->render()
            );

            throw $e;
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    /**
     * @param OrderInterface $order
     * @return string
     * @throws LocalizedException
     */
    private function getFromOrder(OrderInterface $order): string
    {
        // @phpstan-ignore-next-line
        if (!$this->isInPostPickupDeliveryMethod((string)$order->getShippingMethod())) {
            throw new LocalizedException(
                __(
                    'Delivery method selected for this order #%1 is not InPost Paczkomat 24/7',
                    (string)$order->getIncrementId()
                )
            );
        }

        $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId((int)$order->getEntityId());
        $lockerId = $inPostPayOrder->getLockerId();

        if (empty($lockerId)) {
            throw new LocalizedException(
                __('InPost Locker ID not set for order #%1.', (string)$order->getIncrementId())
            );
        }

        return $lockerId;
    }

    /**
     * @param string $carrierMethodCode
     * @return bool
     */
    private function isInPostPickupDeliveryMethod(string $carrierMethodCode): bool
    {
        $inPostPickupCarrierCodes = [];
        foreach ($this->shipmentMappingConfigProvider->getAllDeliveryTypes() as $deliveryType) {
            try {
                $inPostPickupCarrierCodes[] = $this->shipmentMappingConfigProvider->getCarrierMethodCodeForOptions(
                    $deliveryType,
                    ShipmentMappingConfigProvider::OPTION_STANDARD
                );
            } catch (InPostPayInternalException $e) {
                continue;
            }

            foreach ($this->shipmentMappingConfigProvider->getNonStandardDeliveryOptions(true) as $option) {
                try {
                    $inPostPickupCarrierCodes[] = $this->shipmentMappingConfigProvider->getCarrierMethodCodeForOptions(
                        $deliveryType,
                        $option
                    );
                } catch (InPostPayInternalException $e) {
                    continue;
                }
            }
        }

        return in_array($carrierMethodCode, $inPostPickupCarrierCodes);
    }
}
