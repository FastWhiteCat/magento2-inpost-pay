<?php
declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderEventInterface;
use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\EventDataInterface;
use InPost\InPostPay\Api\Data\UpdateOrderResponseInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Exception\OrderNotUpdateException;
use InPost\InPostPay\Model\IziApi\Response\UpdateOrderResponseFactory;
use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use InPost\InPostPay\Service\GetOrderByIncrementId;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderEvent implements OrderEventInterface
{
    public const SKIP_INPOST_PAY_SYNC_FLAG = 'skip_inpost_pay_sync';
    private const PAYMENT_STATUS_AUTHORIZED = 'AUTHORIZED';
    private const INPOST_PAY_METHOD_CODE = 'inpost_pay';
    public const ORDER_STATUS_REJECTED = 'ORDER_REJECTED';
    public const ORDER_STATUS_COMPLETED = 'ORDER_COMPLETED';

    public function __construct(
        private readonly RestRequest $restRequest,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly GeneralConfigProvider $generalConfigProvider,
        private readonly UpdateOrderResponseFactory $updateOrderResponseFactory,
        private readonly GetOrderByIncrementId $getOrderByIncrementId,
        private readonly EventManager $eventManager,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(
        string $orderId,
        string $eventId,
        string $eventDataTime,
        EventDataInterface $eventData,
        ?PhoneNumberInterface $phoneNumber = null
    ): UpdateOrderResponseInterface {
        try {
            $this->eventManager->dispatch('izi_order_update_before', [
                'orderId' => $orderId,
                'eventId' => $eventId,
                'eventDataTime' => $eventDataTime,
                'eventData' => $eventData,
                'phoneNumber' => $phoneNumber
            ]);
            /**
             * @var Order $order
             */
            $order = $this->getOrderByIncrementId->get($orderId);
            $this->checkIfCanProcess($order, $phoneNumber);
            $inPostPayOrderStatus = $this->updateOrder($order, $eventData);

            $this->eventManager->dispatch('izi_order_update_after', [
                'order' => $order,
                'inPostPayOrderStatus' => $inPostPayOrderStatus
            ]);
        } catch (NoSuchEntityException $e) {
            $errorMsg = __('Order not found.');
            $this->logger->error($e->getMessage());

            throw new NoSuchEntityException($errorMsg);
        } catch (OrderNotUpdateException $e) {
            $this->logger->error($e->getMessage());

            throw new OrderNotUpdateException();
        }

        $data = [
            UpdateOrderResponseInterface::ORDER_STATUS => $inPostPayOrderStatus,
            UpdateOrderResponseInterface::ORDER_MERCHANT_STATUS_DESCRIPTION => $order->getStatusLabel(),
            UpdateOrderResponseInterface::DELIVERY_REFERENCES_LIST => $this->getTrackingNumbers($order)
        ];

        return $this->updateOrderResponseFactory->create(['data' => $data]);
    }

    private function checkIfCanProcess(Order $order, ?PhoneNumberInterface $phoneNumber): void
    {
        if ($phoneNumber) {
            $phone = $phoneNumber->getCountryPrefix() . $phoneNumber->getPhone();

            if ($order->getShippingAddress() && $phone !== $order->getShippingAddress()->getTelephone()) {
                throw new NoSuchEntityException(__('Order not found.'));
            }
        }

        if ($order->getPayment() && $order->getPayment()->getMethod() !== self::INPOST_PAY_METHOD_CODE) {
            throw new NoSuchEntityException(__('Order not found.'));
        }
    }

    private function getInPostPayOrder(int $orderId): InPostPayOrderInterface
    {
        try {
            return $this->inPostPayOrderRepository->getByOrderId($orderId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    private function updateOrder(Order $order, EventDataInterface $eventData): string
    {
        $orderEntityId = is_scalar($order->getEntityId()) ? (int)$order->getEntityId() : 0;
        $inPostPayOrder = $this->getInPostPayOrder($orderEntityId);
        $paymentStatus = $eventData->getPaymentStatus();
        $orderStatus = $eventData->getOrderStatus();

        if ($paymentStatus === self::PAYMENT_STATUS_AUTHORIZED) {
            $this->updateOrderPayment($order);
            $this->addOrderCommentAndSave($order);
            $this->updateInPostPayOrderStatus($inPostPayOrder, self::ORDER_STATUS_COMPLETED);
            return self::ORDER_STATUS_COMPLETED;
        }

        if ($orderStatus === self::ORDER_STATUS_REJECTED) {
            $this->updateOrderStatus($order);
            $this->addOrderCommentAndSave($order);
            $this->updateInPostPayOrderStatus($inPostPayOrder, self::ORDER_STATUS_REJECTED);
            return self::ORDER_STATUS_REJECTED;
        }

        throw new OrderNotUpdateException();
    }

    private function updateOrderPayment(Order $order): void
    {
        if ($order->getStatus() === $this->generalConfigProvider->getNewOrderStatus()) {
            $payment = $order->getPayment();
            if ($payment) {
                /** @var \Magento\Sales\Model\Order\Payment $payment */
                $payment->capture();
                $order->setIsInProcess(true);

                return;
            }
        }

        throw new OrderNotUpdateException();
    }

    private function updateOrderStatus(Order $order): void
    {
        if (!$order->isCanceled()) {
            if ($order->canCancel()) {
                $order->cancel();

                return;
            }
        }

        throw new OrderNotUpdateException();
    }

    private function addOrderCommentAndSave(Order $order): void
    {
        $order->addCommentToStatusHistory('Order updated by InPostPay, full request: '
            . $this->restRequest->getContent());

        $order->setData(self::SKIP_INPOST_PAY_SYNC_FLAG, true);
        $this->orderRepository->save($order);
        $orderEntityId = is_scalar($order->getEntityId()) ? (int)$order->getEntityId() : 0;
        $this->logger->info(sprintf('Order with ID %s has been updated.', $orderEntityId));
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

    private function updateInPostPayOrderStatus(InPostPayOrderInterface $inPostPayOrder, string $status): void
    {
        $inPostPayOrder->setOrderStatus($status);
        $this->inPostPayOrderRepository->save($inPostPayOrder);
    }
}
