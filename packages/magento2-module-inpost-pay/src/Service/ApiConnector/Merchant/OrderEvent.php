<?php
declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketUpdateInterface;
use InPost\InPostPay\Api\ApiConnector\Merchant\OrderEventInterface;
use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\EventDataInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderUpdateInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderUpdateInterfaceFactory;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Exception\OrderNotFoundException;
use InPost\InPostPay\Exception\OrderNotUpdateException;
use InPost\InPostPay\Model\Config\Payment\TitleUpdater;
use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use InPost\InPostPay\Service\GetOrderById;
use InPost\InPostPay\Service\GetOrderByIncrementId;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface as TransactionBuilder;
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

    /**
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param GeneralConfigProvider $generalConfigProvider
     * @param OrderUpdateInterfaceFactory $orderUpdateFactory
     * @param GetOrderByIncrementId $getOrderByIncrementId
     * @param GetOrderById $getOrderById
     * @param EventManager $eventManager
     * @param TransactionBuilder $transactionBuilder
     * @param TransactionRepositoryInterface $transactionRepository
     * @param LoggerInterface $logger
     * @param TitleUpdater $titleUpdater
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly GeneralConfigProvider $generalConfigProvider,
        private readonly OrderUpdateInterfaceFactory $orderUpdateFactory,
        private readonly GetOrderByIncrementId $getOrderByIncrementId,
        private readonly GetOrderById $getOrderById,
        private readonly EventManager $eventManager,
        private readonly TransactionBuilder $transactionBuilder,
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly LoggerInterface $logger,
        private readonly TitleUpdater $titleUpdater
    ) {
    }

    public function execute(
        string $orderId,
        string $eventId,
        string $eventDataTime,
        EventDataInterface $eventData,
        ?PhoneNumberInterface $phoneNumber = null
    ): OrderUpdateInterface {
        try {
            $this->eventManager->dispatch('izi_order_update_before', [
                InPostPayOrderInterface::ORDER_ID => $orderId,
                BasketUpdateInterface::EVENT_ID => $eventId,
                BasketUpdateInterface::EVENT_DATA_TIME => $eventDataTime,
                OrderEventInterface::EVENT_DATA => $eventData,
                InPostPayOrderInterface::PHONE_NUMBER => $phoneNumber
            ]);

            /** @var Order $order */
            $order = $this->getOrder($orderId);
            $this->checkIfCanProcess($order, $phoneNumber);
            $inPostPayOrderStatus = $this->updateOrder($order, $eventData);

            $data = [
                OrderUpdateInterface::ORDER_STATUS => $inPostPayOrderStatus,
                OrderUpdateInterface::ORDER_MERCHANT_STATUS_DESCRIPTION => $order->getStatusLabel(),
                OrderUpdateInterface::DELIVERY_REFERENCES_LIST => $this->getTrackingNumbers($order)
            ];

            /** @var OrderUpdateInterface $orderUpdate */
            $orderUpdate = $this->orderUpdateFactory->create(['data' => $data]);

            $this->eventManager->dispatch(
                'izi_order_update_after',
                [OrderEventInterface::ORDER_UPDATE => $orderUpdate]
            );

            return $orderUpdate;
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            throw new OrderNotFoundException();
        } catch (OrderNotUpdateException $e) {
            $this->logger->error($e->getMessage());

            throw new OrderNotUpdateException();
        }
    }

    /**
     * @param string $orderIdentificationNr
     * @return OrderInterface
     * @throws NoSuchEntityException
     */
    private function getOrder(string $orderIdentificationNr): OrderInterface
    {
        try {
            $orderId = (int)$orderIdentificationNr;

            if ((string)$orderId === $orderIdentificationNr) {
                $order = $this->getOrderById->get($orderId);
            } else {
                $order = $this->getOrderByIncrementId->get($orderIdentificationNr);
            }
        } catch (NoSuchEntityException $e) {
            $order = $this->getOrderByIncrementId->get($orderIdentificationNr);
        }

        return $order;
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
            $this->updateOrderPayment($order, $eventData);
            $this->addOrderCommentAndSave($order, $eventData);
            $this->updateInPostPayOrderStatus($inPostPayOrder, self::ORDER_STATUS_COMPLETED);

            $this->logger->debug(
                sprintf('Payment for order #%s has been authorized.', (string)$order->getIncrementId())
            );

            return self::ORDER_STATUS_COMPLETED;
        }

        if ($orderStatus === self::ORDER_STATUS_REJECTED) {
            $this->rejectOrder($order);
            $this->addOrderCommentAndSave($order, $eventData);
            $this->updateInPostPayOrderStatus($inPostPayOrder, self::ORDER_STATUS_REJECTED);

            $this->logger->debug(sprintf('Order #%s has been rejected.', (string)$order->getIncrementId()));

            return self::ORDER_STATUS_REJECTED;
        }

        $this->logger->error(
            sprintf(
                'Payment status[%s] is not authorized and order status[%s] is not rejected. Skipping update.',
                $paymentStatus,
                $orderStatus
            )
        );

        throw new OrderNotUpdateException();
    }

    private function updateOrderPayment(Order $order, EventDataInterface $eventData): void
    {
        $storeId = is_scalar($order->getStoreId()) ? (int)$order->getStoreId() : null;
        $newOrderStatus = $this->generalConfigProvider->getNewOrderStatus($storeId);

        if ($order->getStatus() === $newOrderStatus) {
            $payment = $order->getPayment();
            if ($payment) {
                /** @var \Magento\Sales\Model\Order\Payment $payment */
                $payment->capture();

                $this->addTransaction($payment, $order, $eventData);
                $this->updateOrderPaymentType($payment, $order, $eventData);

                $order->setIsInProcess(true);

                return;
            } else {
                $this->logger->error(
                    sprintf('Order #%s has no payment to authorize.', (string)$order->getIncrementId())
                );
            }
        } else {
            $this->logger->error(
                sprintf(
                    'Order status[%s] is different than status configured for new InPost Pay orders[%s]',
                    $order->getStatus(),
                    $newOrderStatus
                )
            );
        }

        throw new OrderNotUpdateException();
    }

    private function updateOrderPaymentType(
        OrderPaymentInterface $payment,
        Order $order,
        EventDataInterface $eventData
    ): void {
        $paymentType = $eventData->getPaymentType();
        $this->titleUpdater->updatePaymentTitleByType($payment, $order, $paymentType);
    }

    private function rejectOrder(Order $order): void
    {
        if (!$order->isCanceled()) {
            if ($order->canCancel()) {
                $order->cancel();

                return;
            }
        }

        $this->logger->error('Order cannot be canceled, it is either already canceled or not cancelable.');

        throw new OrderNotUpdateException();
    }

    private function addOrderCommentAndSave(Order $order, EventDataInterface $eventData): void
    {
        $paymentCommentData = [
            __('Order updated by InPost Pay:')->render(),
            __('Payment Type: %1', $eventData->getPaymentType())->render(),
            __('Payment Status: %1', $eventData->getPaymentStatus())->render(),
            __('Payment ID: %1', $eventData->getPaymentId())->render(),
            __('Payment Reference Nr: %1', $eventData->getPaymentReference())->render()
        ];

        $order->addCommentToStatusHistory(implode(PHP_EOL, $paymentCommentData));
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

    private function addTransaction(
        OrderPaymentInterface $payment,
        OrderInterface $order,
        EventDataInterface $eventData
    ): void {
        $transaction = $this->transactionBuilder
            ->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($eventData->getPaymentId())
            ->setAdditionalInformation(
                [
                    'reference' => $eventData->getPaymentReference()
                ]
            )
            ->setFailSafe(true)
            ->build(TransactionInterface::TYPE_CAPTURE);

        $this->transactionRepository->save($transaction);
    }
}
