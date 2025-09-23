<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Order\Analytics\Event\Purchase;

use InPost\InPostPay\Api\Data\Order\Analytics\Event\PurchaseEventInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Provider\Config\AnalyticsConfigProvider;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class Ga4PurchaseEvent implements PurchaseEventInterface
{
    public const EVENT_CODE = 'ga4_purchase_event';
    public const AFFILIATION = 'InPost Pay';
    public const ENGAGEMENT_TIME = 100;

    /**
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     * @param AnalyticsConfigProvider $analyticsConfigProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly AnalyticsConfigProvider $analyticsConfigProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return string
     */
    public function getEventCode(): string
    {
        return self::EVENT_CODE;
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getEventData(Order $order): array
    {
        try {
            $orderId = is_scalar($order->getId()) ? (int)$order->getId() : 0;
            $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId($orderId, true);
        } catch (NoSuchEntityException $e) {
            $this->logger->error(
                sprintf(
                    'Could not return event [%s] data for order #%s. Reason: %s',
                    $this->getEventCode(),
                    $order->getIncrementId(),
                    $e->getMessage()
                )
            );

            return [];
        }

        $currentTimestampInMicroseconds = (int) (microtime(true) * 1000000);
        $storeId = (int)$order->getStoreId();
        $gaClientId = $inPostPayOrder->getGaClientId();
        $fbclid = $this->analyticsConfigProvider->isSendingFbclidEnabled($storeId)
            ? $inPostPayOrder->getFbclid() : null;
        $gclid = $this->analyticsConfigProvider->isSendingGclidEnabled($storeId)
            ? $inPostPayOrder->getGclid() : null;

        $eventData = [
            'client_id' => $gaClientId,
            'timestamp_micros' => $currentTimestampInMicroseconds,
            'non_personalized_ads' => false
        ];
        $eventParams = $this->preparePurchaseEventParams($order);

        if ($gclid) {
            $eventParams['gclid'] = $gclid;
        }

        if ($fbclid) {
            $eventParams['fbclid'] = $fbclid;
        }

        $eventData['events'] = [];
        $eventData['events'][] = [
            'name' => 'purchase',
            'params' => $eventParams
        ];

        return $eventData;
    }

    /**
     * @param Order $order
     * @return array
     */
    private function preparePurchaseEventParams(Order $order): array
    {
        $items = [];

        /** @var OrderItemInterface $item */
        foreach ($order->getAllVisibleItems() as $item) {
            $qtyOrdered = (float)$item->getQtyOrdered() > 0 ? (float)$item->getQtyOrdered() : 1.00;
            $itemRowTotalInclTaxAndDiscount = DecimalCalculator::sub(
                (float)$item->getRowTotalInclTax(),
                abs((float)$item->getDiscountAmount())
            );
            $finalUnitPriceInclTax = DecimalCalculator::div(
                $itemRowTotalInclTaxAndDiscount,
                $qtyOrdered
            );

            $items[] = [
                'item_id' => $item->getSku(),
                'item_name' => $item->getName(),
                'price' => $finalUnitPriceInclTax,
                'quantity' => (int)$qtyOrdered,
            ];
        }

        $totalProductsWithTaxAndDiscount = DecimalCalculator::sub(
            (float)$order->getSubtotalInclTax(),
            abs((float)$order->getDiscountAmount())
        );
        $productsTaxAmount = DecimalCalculator::sub(
            (float)$order->getTaxAmount(),
            (float)$order->getShippingTaxAmount()
        );
        $eventParams = [
            'transaction_id' => $order->getIncrementId(),
            'affiliation' => self::AFFILIATION,
            'value' => $totalProductsWithTaxAndDiscount,
            'currency' => $order->getOrderCurrencyCode(),
            'tax' => $productsTaxAmount,
            'shipping' => (float)$order->getShippingInclTax(),
            'engagement_time_msec' => self::ENGAGEMENT_TIME,
            'items' => $items
        ];

        if ($order->getCouponCode()) {
            $eventParams['coupon'] = (string)$order->getCouponCode();
        }

        return $eventParams;
    }
}
