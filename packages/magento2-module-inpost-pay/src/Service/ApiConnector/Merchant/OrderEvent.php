<?php
declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderEventInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Api\Data\UpdateOrderResponseInterface;
use InPost\InPostPay\Exception\OrderNotUpdateException;
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
    public const SKIP_INPOST_PAY_SYNC_FLAG = 'skip_inpost_pay_sync';
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
        try {
            $order = $this->getOrderByIncrementId($orderId);
            $this->checkInPostPayOrderExist((int)$order->getId());

            $payload = $this->jsonSerializer->unserialize((string)$this->restRequest->getContent());
            $payload = is_array($payload) ? $payload : [];

            $phoneNumber = $this->extractPhoneNumber($payload);

            if (($order->getShippingAddress() && $phoneNumber !== $order->getShippingAddress()->getTelephone()) ||
                ($order->getPayment() && $order->getPayment()->getMethod() !== self::INPOST_PAY_METHOD_CODE)
            ) {
                throw new NoSuchEntityException(__('Order not found.'));
            }

            $paymentStatus = $this->extractDataFromEventData($payload, self::PAYMENT_STATUS);
            $orderStatus = $this->extractDataFromEventData($payload, self::ORDER_STATUS);

            if ($paymentStatus === self::PAYMENT_STATUS_AUTHORIZED) {
                $this->updateOrderPayment($order);
            } elseif ($orderStatus === self::ORDER_STATUS_REJECTED) {
                $this->updateOrderStatus($order);
            } else {
                throw new OrderNotUpdateException();
            }

        } catch (NoSuchEntityException $e) {
            $errorMsg = __('Order not found.');
            $this->logger->error($e->getMessage());

            throw new NoSuchEntityException($errorMsg);
        } catch (OrderNotUpdateException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }catch (LocalizedException $e) {
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

    private function checkInPostPayOrderExist(int $orderId): void
    {
        try {
            $this->inPostPayOrderRepository->getByOrderId($orderId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    private function extractDataFromEventData(array $payload, string $dataName): string
    {
        $data = '';
        if (isset($payload[self::EVENT_DATA]) && is_array($payload[self::EVENT_DATA])) {
            $eventData = $payload[self::EVENT_DATA];
            if (isset($eventData[$dataName]) && is_string($eventData[$dataName])) {
                $data = trim((string)$eventData[$dataName]);
            }
        }

        return $data;
    }

    private function extractPhoneNumber(array $payload): string
    {
        $countryPrefix = '';
        $phoneNumber = '';
        if (isset($payload[self::PHONE_NUMBER_PARAM]) && is_array($payload[self::PHONE_NUMBER_PARAM])) {
            $phoneNumberData = $payload[self::PHONE_NUMBER_PARAM];
            if (isset($phoneNumberData[self::PHONE_PARAM]) && is_string($phoneNumberData[self::PHONE_PARAM])) {
                $phoneNumber = trim((string)$phoneNumberData[self::PHONE_PARAM]);
            }

            if (isset($phoneNumberData[self::COUNTRY_PREFIX_PARAM])
                && is_string($phoneNumberData[self::COUNTRY_PREFIX_PARAM])
            ) {
                $countryPrefix = trim((string)$phoneNumberData[self::COUNTRY_PREFIX_PARAM]);
            }
        }

        return $countryPrefix . $phoneNumber;
    }

    private function updateOrderPayment(Order $order): void
    {
        if ($order->getStatus() === $this->generalConfigProvider->getNewOrderStatus()) {
            $payment = $order->getPayment();
            $payment->capture();
            $order->setIsInProcess(true);

            $this->addOrderCommentAndSave($order);
            return;
        }

        throw new OrderNotUpdateException();
    }

    private function updateOrderStatus(Order $order): void
    {
        if (!$order->isCanceled()) {
            if ($order->canCancel()) {
                $order->cancel();
                $this->addOrderCommentAndSave($order);
                return;
            }
        }

        throw new OrderNotUpdateException();
    }

    private function addOrderCommentAndSave(Order $order): void
    {
        $order->addCommentToStatusHistory('Order updated by InPostPay, full request: '
            . (string)$this->restRequest->getContent()
        );

        $order->setData(self::SKIP_INPOST_PAY_SYNC_FLAG, true);
        $order = $this->orderRepository->save($order);
        $this->logger->info(sprintf('Order with ID %s has been updated.', $order->getId()));
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
            case Order::STATE_CANCELED:
                $orderStatus = self::ORDER_STATUS_REJECTED;
                break;
            default:
                $orderStatus = '';
        }

        return $orderStatus;
    }
}
