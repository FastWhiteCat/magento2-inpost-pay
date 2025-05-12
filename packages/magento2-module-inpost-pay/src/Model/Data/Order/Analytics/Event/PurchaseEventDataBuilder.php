<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Order\Analytics\Event;

use InPost\InPostPay\Api\Data\Order\Analytics\Event\PurchaseEventInterface;
use Magento\Sales\Model\Order;

class PurchaseEventDataBuilder
{
    /**
     * @var PurchaseEventInterface[]
     */
    private array $purchaseEvents = [];

    public function __construct(
        array $purchaseEvents = []
    ) {
        $this->initPurchaseEvents($purchaseEvents);
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getPurchaseEventsData(Order $order): array
    {
        $eventsData = [];

        foreach ($this->getPurchaseEvents() as $purchaseEvent) {
            $eventsData[$purchaseEvent->getEventCode()] = $purchaseEvent->getEventData($order);
        }

        return $eventsData;
    }

    /**
     * @return PurchaseEventInterface[]
     */
    public function getPurchaseEvents(): array
    {
        return $this->purchaseEvents;
    }

    private function initPurchaseEvents(array $purchaseEvents): void
    {
        foreach ($purchaseEvents as $purchaseEventCode => $purchaseEvent) {
            if ($purchaseEvent instanceof PurchaseEventInterface
                && $purchaseEvent->getEventCode() === $purchaseEventCode
            ) {
                $this->purchaseEvents[] = $purchaseEvent;
            }
        }
    }
}
