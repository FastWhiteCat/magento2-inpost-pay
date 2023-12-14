<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use InPost\InPostPay\Api\InPostPayLockerIdProviderInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Exception\InPostPayInvalidConfigurationException;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

class InPostPayLockerIdProvider implements InPostPayLockerIdProviderInterface
{
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
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
            return $this->getFromOrder($this->getOrderByIncrementId($orderIncrementId));
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
     * @param string $incrementId
     * @return OrderInterface
     * @throws NoSuchEntityException
     */
    private function getOrderByIncrementId(string $incrementId): OrderInterface
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::INCREMENT_ID, $incrementId)
            ->create();
        $orders = $this->orderRepository->getList($criteria)->getItems();

        if (count($orders)) {
            if (current($orders) instanceof OrderInterface) {
                $order = current($orders);
            }
        }

        if (!isset($order)) {
            throw new NoSuchEntityException(__('Order #%1 not found.'));
        }

        return $order;
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
            } catch (InPostPayInvalidConfigurationException $e) {
                continue;
            }

            foreach ($this->shipmentMappingConfigProvider->getNonStandardDeliveryOptions() as $option) {
                try {
                    $inPostPickupCarrierCodes[] = $this->shipmentMappingConfigProvider->getCarrierMethodCodeForOptions(
                        $deliveryType,
                        $option
                    );
                } catch (InPostPayInvalidConfigurationException $e) {
                    continue;
                }
            }
        }

        return in_array($carrierMethodCode, $inPostPickupCarrierCodes);
    }
}
