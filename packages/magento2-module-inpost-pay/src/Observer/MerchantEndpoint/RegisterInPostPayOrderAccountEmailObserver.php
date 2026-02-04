<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint;

use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Registry\Order\Email\Sender\InPostPayOrderEmailSenderRegistry;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\InPostPayOrderInterfaceFactory;

class RegisterInPostPayOrderAccountEmailObserver implements ObserverInterface
{
    protected string $eventDescription = 'INCOMING: Order Create request';

    /**
     * @param InPostPayOrderInterfaceFactory $inPostPayOrderFactory
     * @param InPostPayOrderEmailSenderRegistry $inPostPayOrderEmailSenderRegistry
     */
    public function __construct(
        private readonly InPostPayOrderInterfaceFactory $inPostPayOrderFactory,
        private readonly InPostPayOrderEmailSenderRegistry $inPostPayOrderEmailSenderRegistry
    ) {
    }

    public function execute(Observer $observer): void
    {
        $this->inPostPayOrderEmailSenderRegistry->unregister();
        $orderDetails = $observer->getEvent()->getData(OrderInterface::ORDER_DETAILS);
        $accountInfo = $observer->getEvent()->getData(OrderInterface::ACCOUNT_INFO);
        $delivery = $observer->getEvent()->getData(OrderInterface::DELIVERY);

        if (!$accountInfo instanceof AccountInfoInterface
            || !$delivery instanceof DeliveryInterface
            || !$orderDetails instanceof OrderDetailsInterface
        ) {
            return;
        }

        /**
         * Simplified object of InPost Pay Order is created and only filled with Account, Delivery and Digital Mail
         * because that is the only required info to make sure an email copy to is sent to InPost Account Owner.
         * That object is not going to be saved.
         */
        /** @var InPostPayOrderInterface $inPostPayOrderSimplifiedObject */
        $inPostPayOrderSimplifiedObject = $this->inPostPayOrderFactory->create();
        $inPostPayOrderSimplifiedObject->setBasketId($orderDetails->getBasketId());

        if ($accountInfo->getMail()) {
            $inPostPayOrderSimplifiedObject->setInPostPayAccountEmail($accountInfo->getMail());
        }

        if ($delivery->getMail()) {
            $inPostPayOrderSimplifiedObject->setDeliveryEmail($delivery->getMail());
        }

        if ($delivery->getDigitalDeliveryEmail()) {
            $inPostPayOrderSimplifiedObject->setDigitalDeliveryEmail($delivery->getDigitalDeliveryEmail());
        }

        $this->inPostPayOrderEmailSenderRegistry->register($inPostPayOrderSimplifiedObject);
    }
}
