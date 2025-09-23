<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder;

use DateTime;
use DateTimeZone;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Provider\Config\AuthConfigProvider;
use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\DataTransfer\OrderToInPostOrderDataTransferInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Model\Order;

class OrderToInPostOrderOrderDetailsDataTransfer implements OrderToInPostOrderDataTransferInterface
{
    public function __construct(
        private readonly AuthConfigProvider $authConfigProvider,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository
    ) {
    }

    public function transfer(Order $order, OrderInterface $inPostOrder): void
    {
        $orderDetails = $inPostOrder->getOrderDetails();
        $orderId = (is_scalar($order->getId())) ? (int)$order->getId() : 0;
        $storeId = (is_scalar($order->getStoreId())) ? (int)$order->getStoreId() : null;
        $inPostPayOrderEntity = $this->getInPostPayOrderByOrderId($orderId);

        $orderDetails->setOrderId((string)$orderId);
        $orderDetails->setCustomerOrderId((string)$order->getIncrementId());
        $orderDetails->setBasketId((string)$inPostPayOrderEntity->getBasketId());
        $orderDetails->setCurrency((string)$order->getOrderCurrencyCode());
        $orderDetails->setPaymentType((string)$inPostPayOrderEntity->getPaymentType());
        $this->transferOrderPrices($order, $orderDetails);
        $deliveryReferenceList = [];
        foreach ($order->getTracksCollection() as $track) {
            if ($track instanceof ShipmentTrackInterface) {
                $trackNr = $track->getTrackNumber();
                if ($trackNr) {
                    $deliveryReferenceList[] = $trackNr;
                }
            }
        }
        if ($deliveryReferenceList) {
            $orderDetails->setDeliveryReferencesList($deliveryReferenceList);
        }
        $createdAtDateTime = new DateTime((string)$order->getCreatedAt(), new DateTimeZone('UTC'));
        $orderDetails->setOrderCreationDate(
            $createdAtDateTime->format(BasketInterface::INPOST_DATE_FORMAT)
        );
        $orderDetails->setOrderComments((string)$order->getCustomerNote());
        $orderDetails->setOrderMerchantStatusDescription($order->getStatusLabel());
        $orderDetails->setPosId($this->authConfigProvider->getPosId($storeId));

        $inPostOrder->setOrderDetails($orderDetails);
    }

    /**
     * @param int $orderId
     * @return InPostPayOrderInterface
     * @throws NoSuchEntityException
     */
    private function getInPostPayOrderByOrderId(int $orderId): InPostPayOrderInterface
    {
        return $this->inPostPayOrderRepository->getByOrderId($orderId);
    }

    private function transferOrderPrices(Order $order, OrderDetailsInterface $orderDetails): void
    {
        $discountInclTax = DecimalCalculator::round((float)$order->getDiscountAmount());
        $discountExclTax = DecimalCalculator::add(
            (float)$order->getDiscountAmount(),
            (float)$order->getDiscountTaxCompensationAmount()
        );
        $priceInclTaxWithoutShipping = DecimalCalculator::add(
            DecimalCalculator::round((float)$order->getSubtotalInclTax()),
            $discountInclTax
        );
        $priceInclTaxWithoutShipping = max($priceInclTaxWithoutShipping, 0);
        $priceExclTaxWithoutShipping = DecimalCalculator::add(
            DecimalCalculator::round((float)$order->getSubtotal()),
            $discountExclTax
        );
        $priceExclTaxWithoutShipping = max($priceExclTaxWithoutShipping, 0);
        $taxWithoutShipping = DecimalCalculator::sub($priceInclTaxWithoutShipping, $priceExclTaxWithoutShipping);

        $orderBasePrice = $orderDetails->getOrderBasePrice();
        $orderBasePrice->setNet($priceExclTaxWithoutShipping);
        $orderBasePrice->setGross($priceInclTaxWithoutShipping);
        $orderBasePrice->setVat($taxWithoutShipping);
        $orderDetails->setOrderBasePrice($orderBasePrice);

        $grandTotalGross = DecimalCalculator::round((float)$order->getGrandTotal());
        $grandTotalTax = DecimalCalculator::round((float)$order->getTaxAmount());
        $grandTotalNet = DecimalCalculator::sub($grandTotalGross, $grandTotalTax);

        $orderFinalPrice = $orderDetails->getOrderFinalPrice();
        $orderFinalPrice->setNet($grandTotalNet);
        $orderFinalPrice->setGross($grandTotalGross);
        $orderFinalPrice->setVat($grandTotalTax);
        $orderDetails->setOrderFinalPrice($orderFinalPrice);
    }
}
