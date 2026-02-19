<?php
declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant;

interface OrderUpdateInterface
{
    public const ORDER_STATUS = 'order_status';
    public const ORDER_MERCHANT_STATUS_DESCRIPTION = 'order_merchant_status_description';
    public const DELIVERY_REFERENCES_LIST = 'delivery_references_list';

    /**
     * @return string|null
     */
    public function getOrderStatus(): ?string;

    /**
     * @param string $orderStatus
     * @return void
     */
    public function setOrderStatus(string $orderStatus): void;

    /**
     * @return string|null
     */
    public function getOrderMerchantStatusDescription(): ?string;

    /**
     * @param string $statusDescription
     * @return void
     */
    public function setOrderMerchantStatusDescription(string $statusDescription): void;

    /**
     * @return string[]|null
     */
    public function getDeliveryReferencesList(): ?array;

    /**
     * @param string[] $deliveryReferencesList
     * @return void
     */
    public function setDeliveryReferencesList(array $deliveryReferencesList): void;
}
