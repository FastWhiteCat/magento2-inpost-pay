<?php
declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderEventInterface;
use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Api\Data\UpdateOrderResponseInterface;
use InPost\InPostPay\Model\IziApi\Response\UpdateOrderResponseFactory;
use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class OrderEvent implements OrderEventInterface
{
    private const EVENT_ID = 'event_id';
    private const EVENT_DATA_TIME = 'event_data_time';

    private const PHONE_NUMBER_PARAM = 'phone_number';
    private const PHONE_PARAM = 'phone';
    private const COUNTRY_PREFIX_PARAM = 'country_prefix';
    private const EVENT_DATA = 'event_data';
    private const PAYMENT_STATUS = 'payment_status';
    private const PAYMENT_STATUS_AUTHORIZED = 'AUTHORIZED';
    private const INPOST_PAY_METHOD_CODE = 'inpost_pay';

    private const ORDER_STATUS = 'order_status';
    private const ORDER_STATUS_PROCESSING = 'ORDER_PROCESSING';
    private const ORDER_STATUS_REJECTED = 'ORDER_REJECTED';
    private const ORDER_STATUS_COMPLETED = 'ORDER_COMPLETED';

    private const PAYMENT_ID = 'payment_id';
    private const PAYMENT_REFERENCE = 'payment_reference';
    private const PAYMENT_TYPE = 'payment_type';

    public function __construct(
        private readonly RestRequest $restRequest,
        private readonly JsonSerializer $jsonSerializer,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly GeneralConfigProvider $generalConfigProvider,
        private readonly UpdateOrderResponseFactory $updateOrderResponseFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(string $orderId): UpdateOrderResponseInterface
    {
        $data = [];

        try {
            $order = $this->getOrderByIncrementId($orderId);
            $this->getInPostPayOrder((int)$order->getId());

            $payload = $this->jsonSerializer->unserialize((string)$this->restRequest->getContent());
            $payload = is_array($payload) ? $payload : [];

            $paymentStatus = $this->extractPaymentStatus($payload);

            if ($order->getStatus() === $this->generalConfigProvider->getNewOrderStatus()
                && $paymentStatus === self::PAYMENT_STATUS_AUTHORIZED
                && $order->getPayment()
                && $order->getPayment()->getMethod() === self::INPOST_PAY_METHOD_CODE
            ) {
                $payment = $order->getPayment();
                $payment->capture();

                $order->addCommentToStatusHistory('Order updated by rest API, full request: '
                    . (string)$this->restRequest->getContent()
                );

                $order = $this->orderRepository->save($order);
                $this->logger->info(sprintf('Order with ID %s has been updated.', $orderId));
            }

        } catch (NoSuchEntityException $e) {
            $errorMsg = __('Basket not found.');
            $this->logger->error($e->getMessage());

            throw new NoSuchEntityException($errorMsg);
        } catch (LocalizedException $e) {
            $errorMsg = __('Cannot update order. Reason: %1', $e->getMessage());
            $this->logger->error($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }

        $data = [
            UpdateOrderResponseInterface::ORDER_STATUS => $this->getInPostOrderStatus($order),
            UpdateOrderResponseInterface::ORDER_MERCHANT_STATUS_DESCRIPTION => $order->getStatusLabel(),
            UpdateOrderResponseInterface::DELIVERY_REFERENCES_LIST => $this->getTrackingNumbers($order)
        ];

        return $this->updateOrderResponseFactory->create(['data' => $data]);
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
     * @param int $orderId
     * @return InPostPayOrderInterface
     * @throws LocalizedException
     */
    private function getInPostPayOrder(int $orderId): InPostPayOrderInterface
    {
        try {
            return $this->inPostPayOrderRepository->getByOrderId($orderId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    private function extractPaymentStatus(array $payload): string
    {
        $paymentStatus = '';
        if (isset($payload[self::EVENT_DATA]) && is_array($payload[self::EVENT_DATA])) {
            $eventData = $payload[self::EVENT_DATA];
            if (isset($eventData[self::PAYMENT_STATUS]) && is_string($eventData[self::PAYMENT_STATUS])) {
                $paymentStatus = trim((string)$eventData[self::PAYMENT_STATUS]);
            }
        }

        return $paymentStatus;
    }

    private function getTrackingNumbers(Order $order): array
    {
        $tracksCollection = $order->getTracksCollection();
        $trackNumbers = [];

        foreach ($tracksCollection->getItems() as $track) {
            $trackNumbers[] = $track->getTrackNumber();
        }

        return $trackNumbers;
    }

    private function getInPostOrderStatus(Order $order): string
    {
        switch ($order->getStatus()) {
            case Order::STATE_PROCESSING:
                $orderStatus = self::ORDER_STATUS_PROCESSING;
                break;
            case Order::STATE_COMPLETE:
            case Order::STATE_CLOSED:
                $orderStatus = self::ORDER_STATUS_COMPLETED;
                break;
            case Order::STATE_CANCELED:
                $orderStatus = self::ORDER_STATUS_REJECTED;
                break;
            default:
                $orderStatus = '';
        }

        return $orderStatus;

    }
}
