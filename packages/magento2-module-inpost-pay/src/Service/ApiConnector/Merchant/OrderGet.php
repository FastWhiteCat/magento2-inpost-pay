<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderCreateInterface;
use InPost\InPostPay\Service\GetOrderById;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Api\Data\OrderInterface as MagentoOrderInterface;
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
    public function __construct(
        private readonly GetOrderByIncrementId $getOrderByIncrementId,
        private readonly GetOrderById $getOrderById,
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
        try {
            $this->eventManager->dispatch('izi_order_get_before', [OrderGetInterface::ORDER_ID => $orderId]);

            $order = $this->getOrder($orderId);

            if ($order instanceof Order) {
                /** @var OrderInterface $inPostOrder */
                $inPostOrder = $this->orderFactory->create();
                $this->orderToInPostOrderDataTransfer->transfer($order, $inPostOrder);

                $this->eventManager->dispatch(
                    'izi_order_get_after',
                    [OrderCreateInterface::INPOST_ORDER => $inPostOrder]
                );

                return $inPostOrder;
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
    }

    /**
     * @param string $orderIdentificationNr
     * @return MagentoOrderInterface
     * @throws NoSuchEntityException
     */
    private function getOrder(string $orderIdentificationNr): MagentoOrderInterface
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
}
