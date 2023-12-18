<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderCreateInterface;
use InPost\InPostPay\Api\ApiConnector\Merchant\OrderEventInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Throwable;
use InPost\InPostPay\Api\ApiConnector\Merchant\OrderGetInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Exception\InPostPayAuthorizationException;
use InPost\InPostPay\Exception\InPostPayBadRequestException;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Exception\OrderNotFoundException;
use InPost\InPostPay\Service\DataTransfer\OrderToInPostOrderDataTransfer;
use InPost\InPostPay\Service\GetOrderByIncrementId;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderGet implements OrderGetInterface
{
    private const REQUEST_PREFIX = 'ORDER_GET_REQUEST';

    public function __construct(
        private readonly GetOrderByIncrementId $getOrderByIncrementId,
        private readonly OrderToInPostOrderDataTransfer $orderToInPostOrderDataTransfer,
        private readonly OrderInterfaceFactory $orderFactory,
        private readonly EventManager $eventManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param string $orderId
     * @return OrderInterface
     * @throws InPostPayBadRequestException
     * @throws InPostPayAuthorizationException
     * @throws OrderNotFoundException
     * @throws InPostPayInternalException
     */
    public function execute(string $orderId): OrderInterface
    {
        $this->eventManager->dispatch('izi_order_get_before', ['order_id' => $orderId]);
        $this->createRequestDebugLog(sprintf('Retrieving order data for Order ID: %s', $orderId));
        try {
            $order = $this->getOrderByIncrementId->get($orderId);
            if ($order instanceof Order) {
                /** @var OrderInterface $inPostOrder */
                $inPostOrder = $this->orderFactory->create();
                $this->orderToInPostOrderDataTransfer->transfer($order, $inPostOrder);
                $this->eventManager->dispatch('izi_order_get_after', [
                    OrderEventInterface::ORDER => $order,
                    OrderCreateInterface::INPOST_ORDER => $inPostOrder
                ]);
            } else {
                throw new NoSuchEntityException(__('Order %1 not found.', $orderId));
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            throw new OrderNotFoundException();
        } catch (InPostPayAuthorizationException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw new InPostPayBadRequestException();
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage());

            throw new InPostPayInternalException();
        }

        $this->createRequestDebugLog(sprintf('Order data for Order ID: %s has been retrieved.', $orderId));

        return $inPostOrder;
    }

    private function createRequestDebugLog(string $message): void
    {
        $this->logger->debug(sprintf('%s: %s', self::REQUEST_PREFIX, $message));
    }
}
