<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use InPost\InPostPay\Api\InPostPayLockerIdProviderInterface;
use InPost\InPostPay\Exception\InPostPayInvalidConfigurationException;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

class InPostPayLockerIdProvider implements InPostPayLockerIdProviderInterface
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    public function getFromQuoteById(int $quoteId): string
    {
        $quote = $this->getQuoteById($quoteId);
        // @phpstan-ignore-next-line
        if (!$this->isInPostPickupDeliveryMethod((string)$quote->getShippingAddress()->getShippingMethod())) {
            $errorPhrase = __(
                'Delivery method selected for this quote (ID: %1) is not InPost Paczkomat 24/7',
                (string)$quoteId
            );
            $this->logger->error($errorPhrase->render());

            throw new LocalizedException($errorPhrase);
        }

        $inPostLockerId = (string)$quote->getData(InPostPayLockerIdProviderInterface::INPOST_LOCKER_ID_FIELD);
        $inPostPayLockerId = (string)$quote->getData(
            InPostPayLockerIdProviderInterface::INPOST_PAY_LOCKER_ID_FIELD
        );
        if ($inPostLockerId) {
            $lockerId = $inPostLockerId;
        } elseif ($inPostPayLockerId) {
            $lockerId = $inPostPayLockerId;
        } else {
            throw new LocalizedException(__('InPost Locker not set for Quote ID %1.', (string)$quote->getId()));
        }

        return $lockerId;
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

        $inPostLockerId = (string)$order->getData(InPostPayLockerIdProviderInterface::INPOST_LOCKER_ID_FIELD);
        $inPostPayLockerId = (string)$order->getData(InPostPayLockerIdProviderInterface::INPOST_PAY_LOCKER_ID_FIELD);
        if ($inPostLockerId) {
            $lockerId = $inPostLockerId;
        } elseif ($inPostPayLockerId) {
            $lockerId = $inPostPayLockerId;
        } else {
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
     * @param int $quoteId
     * @return CartInterface
     * @throws NoSuchEntityException
     */
    private function getQuoteById(int $quoteId): CartInterface
    {
        try {
            return $this->cartRepository->get($quoteId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error(
                __('InPost Locker cannot be obtained because quote does not exist: %1', $e->getMessage())->render()
            );

            throw $e;
        }
    }

    /**
     * @param string $carrierMethodCode
     * @return bool
     * @throws InPostPayInvalidConfigurationException
     */
    private function isInPostPickupDeliveryMethod(string $carrierMethodCode): bool
    {
        $inPostPickupCarrierCodes = [
            self::INPOST_PICKUP_CARRIER_CODE,
            $this->shipmentMappingConfigProvider->getCarrierMethodCodeForInPostPickup()
        ];

        return in_array($carrierMethodCode, $inPostPickupCarrierCodes);
    }
}
