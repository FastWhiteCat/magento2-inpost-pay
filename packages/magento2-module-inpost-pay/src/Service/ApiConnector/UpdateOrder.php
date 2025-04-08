<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Model\IziApi\Request\UpdateOrderRequestFactory;
use InPost\InPostPay\Model\IziApi\Request\UpdateOrderRequest;
use InPost\InPostPay\Service\ApiConnector\Merchant\OrderEvent;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class UpdateOrder
{
    public const DEFAULT_DATE_FORMAT = "Y-m-d\TH:i:s.000\Z";

    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly UpdateOrderRequestFactory $updateOrderRequestFactory,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly TimezoneInterface $localeDate,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Order $order
     * @param InPostPayOrderInterface $inPostPayOrder
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Order $order, InPostPayOrderInterface $inPostPayOrder): void
    {
        /** @var UpdateOrderRequest $request */
        $request = $this->updateOrderRequestFactory->create();
        $eventData = [
            'order_merchant_status_description' => $order->getStatusLabel(),
            'delivery_references_list' => $this->getTrackingNumbers($order),
        ];

        if (!$inPostPayOrder->getOrderStatus()) {
            $orderStatus = is_scalar($order->getStatus()) ? (string)$order->getStatus() : '';
            $status = $this->getInPostPayOrderStatus($orderStatus);
            if ($status) {
                $inPostPayOrder->setOrderStatus($status);
                $this->inPostPayOrderRepository->save($inPostPayOrder);
                $eventData['order_status'] = $status;
            }
        }

        $storeId = is_scalar($order->getStoreId()) ? (int)$order->getStoreId() : 0;
        $request->setStoreId($storeId);
        $request->setParams([
            'order_id' => (string)$order->getId(), //@phpstan-ignore-line
            'event_id' => uniqid(),
            'event_data_time' => $this->localeDate->date()->format(self::DEFAULT_DATE_FORMAT),
            'phone_number' => [
                'country_prefix' => (string)$inPostPayOrder->getCountryPrefix(),
                'phone' => (string)$inPostPayOrder->getPhone(),
            ],
            'event_data' => $eventData
        ]);

        try {
            $this->connector->sendRequest($request);
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with updating order. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }

    private function getTrackingNumbers(Order $order): array
    {
        $tracksCollection = $order->getTracksCollection();
        $trackNumbers = [];

        foreach ($tracksCollection->getItems() as $track) {
            $trackNumbers[] = $track->getTrackNumber();
        }

        return array_unique($trackNumbers);
    }

    private function getInPostPayOrderStatus(string $status): ?string
    {
        switch ($status) {
            case Order::STATE_CANCELED:
                return OrderEvent::ORDER_STATUS_REJECTED;
            case Order::STATE_PROCESSING:
                return OrderEvent::ORDER_STATUS_COMPLETED;
            default:
                return null;
        }
    }
}
