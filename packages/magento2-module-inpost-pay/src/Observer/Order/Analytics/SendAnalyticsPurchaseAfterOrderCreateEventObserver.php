<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Order\Analytics;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderCreateInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Model\Data\Order\Analytics\Event\PurchaseEventDataBuilder;
use InPost\InPostPay\Provider\Config\AnalyticsConfigProvider;
use InPost\InPostPay\Service\Order\Analytics\Event\PurchaseEventDataSender;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class SendAnalyticsPurchaseAfterOrderCreateEventObserver implements ObserverInterface
{
    /**
     * @param AnalyticsConfigProvider $analyticsConfigProvider
     * @param PurchaseEventDataBuilder $purchaseEventDataBuilder
     * @param PurchaseEventDataSender $purchaseEventDataSender
     * @param OrderRepositoryInterface $orderRepository
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly AnalyticsConfigProvider $analyticsConfigProvider,
        private readonly PurchaseEventDataBuilder $purchaseEventDataBuilder,
        private readonly PurchaseEventDataSender $purchaseEventDataSender,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer): void
    {
        $inPostOrder = $observer->getEvent()->getData(OrderCreateInterface::INPOST_ORDER);

        if (!$inPostOrder instanceof OrderInterface) {
            return;
        }

        try {
            $orderId = (int)$inPostOrder->getOrderDetails()->getOrderId();
            $magentoOrder = $this->orderRepository->get($orderId);

            if (!$magentoOrder instanceof Order
                || !$this->analyticsConfigProvider->isAnalyticsEnabled((int)$magentoOrder->getStoreId())
            ) {
                return;
            }

            $storeId = (int)$magentoOrder->getStoreId();
            $magentoOrderId = is_scalar($magentoOrder->getId()) ? (int)$magentoOrder->getId() : 0;
            $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId($magentoOrderId);
            $inPostPayOrder->setSerializedAnalyticsData($this->prepareSerializedAnalyticsDataForOrder($magentoOrder));
            $this->inPostPayOrderRepository->save($inPostPayOrder);

            $serializedAnalyticsData = $inPostPayOrder->getSerializedAnalyticsData();
            $eventsData = $this->serializer->unserialize(
                is_scalar($serializedAnalyticsData) ? (string)$serializedAnalyticsData : '[]'
            );
            $eventsData = is_array($eventsData) ? $eventsData : [];

            if (!$this->analyticsConfigProvider->isAsyncSendingEnabled() && !empty($eventsData)) {
                $this->purchaseEventDataSender->sendEventsData(
                    $eventsData,
                    $orderId,
                    $storeId
                );
            }
        } catch (NoSuchEntityException | CouldNotSaveException $e) {
            return;
        }
    }

    /**
     * @param Order $order
     * @return string
     */
    private function prepareSerializedAnalyticsDataForOrder(Order $order): string
    {
        $purchaseEventsData = $this->purchaseEventDataBuilder->getPurchaseEventsData($order);

        return (string)$this->serializer->serialize($purchaseEventsData);
    }
}
