<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant;

use InPost\InPostPay\Api\Data\Merchant\OrderUpdateInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class OrderUpdate extends DataObject implements OrderUpdateInterface, ExtensibleDataInterface
{
    public function getOrderStatus(): ?string
    {
        $orderStatus = $this->getData(self::ORDER_STATUS);

        return is_scalar($orderStatus) ? (string)$orderStatus : null;
    }

    public function setOrderStatus(string $orderStatus): void
    {
        $this->setData(self::ORDER_STATUS, $orderStatus);
    }

    public function getOrderMerchantStatusDescription(): ?string
    {
        $statusDescription = $this->getData(self::ORDER_MERCHANT_STATUS_DESCRIPTION);

        return is_scalar($statusDescription) ? (string)$statusDescription : null;
    }

    public function setOrderMerchantStatusDescription(string $statusDescription): void
    {
        $this->setData(self::ORDER_MERCHANT_STATUS_DESCRIPTION, $statusDescription);
    }

    public function getDeliveryReferencesList(): ?array
    {
        $deliveryReferencesList = $this->getData(self::DELIVERY_REFERENCES_LIST);

        return is_array($deliveryReferencesList) ? $deliveryReferencesList : null;
    }

    public function setDeliveryReferencesList(array $deliveryReferencesList): void
    {
        $this->setData(self::DELIVERY_REFERENCES_LIST, $deliveryReferencesList);
    }
}
