<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Analytics\Event;

use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Api\Order\Analytics\Event\Purchase\EventDataHandlerInterface;
use InPost\InPostPay\Exception\UnableToSendInPostPayAnalyticsDataException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class PurchaseEventDataSender
{
    /**
     * @var EventDataHandlerInterface[]
     */
    private array $eventDataHandlers = [];

    /**
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     * @param LoggerInterface $logger
     * @param array $eventDataHandlers
     */
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly LoggerInterface $logger,
        array $eventDataHandlers = []
    ) {
        $this->initEventDataHandler($eventDataHandlers);
    }

    /**
     * @param array $eventsData
     * @param int $orderId
     * @param int $storeId
     * @return void
     */
    public function sendEventsData(array $eventsData, int $orderId, int $storeId): void
    {
        try {
            $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId($orderId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error(
                sprintf(
                    'Unable to send analytics data. InPost pay order with order ID: %s not found. Error: %s',
                    $orderId,
                    $e->getMessage()
                )
            );

            return;
        }

        foreach ($eventsData as $eventCode => $eventData) {
            $handler = $this->getEventHandlerByCode($eventCode);

            if ($handler) {
                try {
                    $handler->send($eventData, $storeId);
                    $this->logger->debug(
                        sprintf('Analytics data for InPost pay order with order ID: %s has been sent.', $orderId)
                    );
                } catch (UnableToSendInPostPayAnalyticsDataException $e) {
                    $this->logger->error(
                        sprintf(
                            'Unable to send analytics data for InPost pay order with order ID: %s. Error: %s',
                            $orderId,
                            $e->getMessage()
                        )
                    );
                }
            }
        }

        try {
            $inPostPayOrder->setAnalyticsSentAt(date('Y-m-d H:i:s'));
            $this->inPostPayOrderRepository->save($inPostPayOrder);
        } catch (CouldNotSaveException $e) {
            $this->logger->error(
                sprintf(
                    'Unable to save analytics_sent_at for InPost pay order with order ID: %s. Error: %s',
                    $orderId,
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @param string $eventCode
     * @return EventDataHandlerInterface|null
     */
    public function getEventHandlerByCode(string $eventCode): ?EventDataHandlerInterface
    {
        return $this->eventDataHandlers[$eventCode] ?? null;
    }

    /**
     * @param array $eventDataHandlers
     * @return void
     */
    private function initEventDataHandler(array $eventDataHandlers): void
    {
        foreach ($eventDataHandlers as $eventDataHandlerCode => $eventDataHandler) {
            if ($eventDataHandler instanceof EventDataHandlerInterface
                && $eventDataHandlerCode === $eventDataHandler->getEventCode()
            ) {
                $this->eventDataHandlers[$eventDataHandler->getEventCode()] = $eventDataHandler;
            }
        }
    }
}
