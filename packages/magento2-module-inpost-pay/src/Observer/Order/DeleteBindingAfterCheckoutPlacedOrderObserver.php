<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Order;

use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;

class DeleteBindingAfterCheckoutPlacedOrderObserver extends DeleteBindingAfterPlaceOrderObserver
{
    /**
     * This observer is used to delete the existing binding after the order is placed in the checkout only.
     * If it was placed via InPost Pay API, the unnecessary bindings will be deleted in separated event
     * after processing all defined InPost Pay order creation steps, not earlier.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getData('order');
        $registeredBasketId = $this->orderCreationRegistry->registry();

        if ($order instanceof Order
            && $registeredBasketId === null
            && $this->canSync($order)
        ) {
            parent::execute($observer);
            $this->logger->debug(
                'Remaining InPost Pay Basket Binding has been deleted after the order was placed via checkout.'
            );
        }
    }
}
