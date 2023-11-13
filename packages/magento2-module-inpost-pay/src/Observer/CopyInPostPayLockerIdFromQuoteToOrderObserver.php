<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer;

use InPost\InPostPay\Api\InPostPayLockerIdProviderInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;

class CopyInPostPayLockerIdFromQuoteToOrderObserver implements ObserverInterface
{
    public function __construct(
        private readonly InPostPayLockerIdProviderInterface $inPostPayLockerIdProvider
    ) {
    }

    /**
     * Locker ID will be gathered from quote and moved to order
     * Source value is either quote.inpost_locker_id or quote.inpost_pay_locker_id
     * depending on official InPost Delivery module is installed and that column is fulfilled
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getData('order');
        if ($order instanceof Order) {
            $quoteInPostLockerId = $this->inPostPayLockerIdProvider->getFromQuoteById((int)$order->getQuoteId());
            $order->setData(InPostPayLockerIdProviderInterface::INPOST_PAY_LOCKER_ID_FIELD, $quoteInPostLockerId);
        }
    }
}
