<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer;

use InPost\InPostPay\Api\InPostPayLockerIdProviderInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Payment\Model\Method\Adapter as InPostPayAdapter;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Psr\Log\LoggerInterface;

class CopyInPostPayLockerIdFromQuoteToOrderObserver implements ObserverInterface
{
    public function __construct(
        private readonly InPostPayLockerIdProviderInterface $inPostPayLockerIdProvider,
        private readonly InPostPayAdapter $inPostPayAdapter,
        private readonly LoggerInterface $logger
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
        if ($order instanceof Order && $this->isOrderApplicable($order)) {
            $quoteInPostLockerId = $this->inPostPayLockerIdProvider->getFromQuoteById((int)$order->getQuoteId());
            $order->setData(InPostPayLockerIdProviderInterface::INPOST_PAY_LOCKER_ID_FIELD, $quoteInPostLockerId);
            $this->logger->info(
                sprintf(
                    'Successfully copied Locker %s from Quote to Order #%s.',
                    $quoteInPostLockerId,
                    (string)$order->getIncrementId()
                )
            );
        }
    }

    private function isOrderApplicable(Order $order): bool
    {
        $payment = $order->getPayment();
        if ($payment instanceof OrderPaymentInterface) {
            // @phpstan-ignore-next-line
            $orderPaymentCode = $payment->getMethodInstance()->getCode();
        }

        return isset($orderPaymentCode) && $orderPaymentCode === $this->inPostPayAdapter->getCode();
    }
}
