<?php

declare(strict_types=1);

namespace InPost\InPostPay\Cron;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Provider\Config\AnalyticsConfigProvider;
use InPost\InPostPay\Service\Order\Analytics\Event\PurchaseEventDataSender;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use InPost\InPostPay\Model\ResourceModel\InPostPayOrder\CollectionFactory as InPostPayOrderCollectionFactory;
use InPost\InPostPay\Model\ResourceModel\InPostPayOrder\Collection as InPostPayOrderCollection;
use Psr\Log\LoggerInterface;

class SendAnalyticsData
{
    /**
     * @param AnalyticsConfigProvider $analyticsConfigProvider
     * @param InPostPayOrderCollectionFactory $inPostPayOrderCollectionFactory
     * @param PurchaseEventDataSender $purchaseEventDataSender
     * @param OrderRepositoryInterface $orderRepository
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly AnalyticsConfigProvider $analyticsConfigProvider,
        private readonly InPostPayOrderCollectionFactory $inPostPayOrderCollectionFactory,
        private readonly PurchaseEventDataSender $purchaseEventDataSender,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Sends InPost Pay related analytics events data
     *
     * @return void
     */
    public function execute(): void
    {
        $unsentInPostPayOrders = $this->getUnsentInPostPayOrders();
        $this->logger->debug(
            sprintf(
                'InPost Pay Orders Analytics Data Sender CRON has started with %s orders to process.',
                count($unsentInPostPayOrders)
            )
        );

        foreach ($unsentInPostPayOrders as $inPostPayOrder) {
            $order = $this->getOrderById((int)$inPostPayOrder->getOrderId());
            $serializedAnalyticsData = $inPostPayOrder->getSerializedAnalyticsData();
            $analyticsData = $this->serializer->unserialize(
                is_scalar($serializedAnalyticsData) ? (string)$serializedAnalyticsData : '[]'
            );

            if (!$order instanceof OrderInterface
                || !$this->analyticsConfigProvider->isAnalyticsEnabled((int)$order->getStoreId())
                || !$this->analyticsConfigProvider->isAsyncSendingEnabled()
                || !is_array($analyticsData)
                || empty($analyticsData)
            ) {
                continue;
            }

            $this->purchaseEventDataSender->sendEventsData(
                $analyticsData,
                (int)$inPostPayOrder->getOrderId(),
                (int)$order->getStoreId()
            );
        }

        $this->logger->debug('InPost Pay Orders Analytics Data Sender CRON has finished.');
    }

    /**
     * @return array
     */
    private function getUnsentInPostPayOrders(): array
    {
        /** @var InPostPayOrderCollection $collection */
        $collection = $this->inPostPayOrderCollectionFactory->create();
        $collection->addFieldToFilter(InPostPayOrderInterface::ANALYTICS_SENT_AT, ['null' => true]);
        $collection->addFieldToFilter(InPostPayOrderInterface::SERIALIZED_ANALYTICS_DATA, ['notnull' => true]);
        $result = [];

        foreach ($collection->getItems() as $inPostPayOrder) {
            if ($inPostPayOrder instanceof InPostPayOrderInterface) {
                $result[] = $inPostPayOrder;
            }
        }

        return $result;
    }

    /**
     * @param int $orderId
     * @return OrderInterface|null
     */
    private function getOrderById(int $orderId): ?OrderInterface
    {
        try {
            return $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
