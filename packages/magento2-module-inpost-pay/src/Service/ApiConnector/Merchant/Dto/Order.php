<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant\Dto;

use InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order\AccountInfo;
use InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order\Consent;
use InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order\Delivery;
use InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order\OrderDetails;

class Order
{
    public const ORDER_DETAILS = 'order_details';
    public const ACCOUNT_INFO = 'account_info';
    public const DELIVERY = 'delivery';
    public const CONSENTS = 'consents';

    private OrderDetails $orderDetails;
    private AccountInfo $accountInfo;
    private Delivery $delivery;
    private array $consents;

    public function getOrderDetails(): OrderDetails
    {
        return $this->orderDetails;
    }

    public function setOrderDetails(OrderDetails $orderDetails): void
    {
        $this->orderDetails = $orderDetails;
    }

    public function getAccountInfo(): AccountInfo
    {
        return $this->accountInfo;
    }

    public function setAccountInfo(AccountInfo $accountInfo): void
    {
        $this->accountInfo = $accountInfo;
    }

    public function getDelivery(): Delivery
    {
        return $this->delivery;
    }

    public function setDelivery(Delivery $delivery): void
    {
        $this->delivery = $delivery;
    }

    /**
     * @return Consent[]
     */
    public function getConsents(): array
    {
        return $this->consents;
    }

    /**
     * @param Consent[] $consents
     * @return void
     */
    public function setConsents(array $consents): void
    {
        $this->consents = $consents;
    }
}
