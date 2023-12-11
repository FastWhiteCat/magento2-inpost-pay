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
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class OrderEvent implements OrderEventInterface
{
    public const SKIP_INPOST_PAY_SYNC_FLAG = 'skip_inpost_pay_sync';
    private const PAYMENT_STATUS_AUTHORIZED = 'AUTHORIZED';
    private const INPOST_PAY_METHOD_CODE = 'inpost_pay';
    private const ORDER_STATUS_REJECTED = 'ORDER_REJECTED';
    private const ORDER_STATUS_COMPLETED = 'ORDER_COMPLETED';

    public function __construct(
        private readonly RestRequest $restRequest,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly GeneralConfigProvider $generalConfigProvider,
        private readonly UpdateOrderResponseFactory $updateOrderResponseFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(
        string $orderId,
        string $eventId,
        string $eventDataTime,
        PhoneNumberInterface $phoneNumber,
        EventDataInterface $eventData
    ): UpdateOrderResponseInterface {
        try {
            /**
             * @var Order $order
             */
            $order = $this->getOrderByIncrementId($orderId);
            $orderEntityId = is_scalar($order->getEntityId()) ? (int)$order->getEntityId() : 0;
            $inPostPayOrder = $this->getInPostPayOrder($orderEntityId);

            $phone = $phoneNumber->getCountryPrefix() . $phoneNumber->getPhone();

            if (($order->getShippingAddress() && $phone !== $order->getShippingAddress()->getTelephone()) ||
                ($order->getPayment() && $order->getPayment()->getMethod() !== self::INPOST_PAY_METHOD_CODE)
            ) {
                throw new NoSuchEntityException(__('Order not found.'));
            }

            $paymentStatus = $eventData->getPaymentStatus();
            $orderStatus = $eventData->getOrderStatus();

            if ($paymentStatus === self::PAYMENT_STATUS_AUTHORIZED) {
                $this->updateOrderPayment($order);
                $this->addOrderCommentAndSave($order);
                $this->updateInPostPayOrderStatus($inPostPayOrder, self::ORDER_STATUS_COMPLETED);
                $inPostPayOrderStatus = self::ORDER_STATUS_COMPLETED;
            } elseif ($orderStatus === self::ORDER_STATUS_REJECTED) {
                $this->updateOrderStatus($order);
                $this->addOrderCommentAndSave($order);
                $this->updateInPostPayOrderStatus($inPostPayOrder, self::ORDER_STATUS_REJECTED);
                $inPostPayOrderStatus = self::ORDER_STATUS_REJECTED;
            } else {
                throw new OrderNotUpdateException();
            }

        } catch (NoSuchEntityException $e) {
            $errorMsg = __('Order not found.');
            $this->logger->error($e->getMessage());

            throw new NoSuchEntityException($errorMsg);
        } catch (OrderNotUpdateException $e) {
            $this->logger->error($e->getMessage());

            throw new OrderNotUpdateException();
        } catch (LocalizedException $e) {
            $errorMsg = __('Cannot update order. Reason: %1', $e->getMessage());
            $this->logger->error($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }

        $data = [
            UpdateOrderResponseInterface::ORDER_STATUS => $inPostPayOrderStatus,
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

    private function getInPostPayOrder(int $orderId): InPostPayOrderInterface
    {
        try {
            return $this->inPostPayOrderRepository->getByOrderId($orderId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        }
    }

    private function updateOrderPayment(Order $order): void
    {
        if ($order->getStatus() === $this->generalConfigProvider->getNewOrderStatus()) {
            $payment = $order->getPayment();
            if ($payment) {
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

    private function updateInPostPayOrderStatus(InPostPayOrderInterface $inPostPayOrder, string $status): void
    {
        $inPostPayOrder->setOrderStatus($status);
        $this->inPostPayOrderRepository->save($inPostPayOrder);
    }
}
