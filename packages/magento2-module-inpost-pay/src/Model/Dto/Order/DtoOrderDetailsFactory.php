<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Dto\Order;

use InPost\InPostPay\Model\Dto\Order\BasketPriceFactory;
use InPost\InPostPay\Model\Dto\Order\OrderDetailsFactory;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;

class DtoOrderDetailsFactory
{
    public function __construct(
        private readonly OrderDetailsFactory $orderDetailsFactory,
        private readonly BasketPriceFactory $basketPriceFactory
    ) {
    }

    public function create(array $data): OrderDetails
    {
        /** @var OrderDetails $orderDetails */
        $orderDetails = $this->orderDetailsFactory->create();
        $basketPriceData = [];


        if (isset($data[OrderDetails::BASKET_ID]) && is_scalar($data[OrderDetails::BASKET_ID])) {
            $orderDetails->setBasketId((string)$data[OrderDetails::BASKET_ID]);
        }

        if (isset($data[OrderDetails::CURRENCY]) && is_scalar($data[OrderDetails::CURRENCY])) {
            $orderDetails->setCurrency((string)$data[OrderDetails::CURRENCY]);
        }

        if (isset($data[OrderDetails::PAYMENT_TYPE]) && is_scalar($data[OrderDetails::PAYMENT_TYPE])) {
            $orderDetails->setPaymentType((string)$data[OrderDetails::PAYMENT_TYPE]);
        }

        if (isset($data[OrderDetails::BASKET_PRICE]) && is_array($data[OrderDetails::BASKET_PRICE])) {
            $basketPriceData = (array)$data[OrderDetails::BASKET_PRICE];
        }

        $orderDetails->setOrderComments($this->prepareOrderComments($data));
        $orderDetails->setBasketPrice($this->prepareBasketPrice($basketPriceData));

        return $orderDetails;
    }

    private function prepareBasketPrice(array $data): BasketPrice
    {
        /** @var BasketPrice $basketPrice */
        $basketPrice = $this->basketPriceFactory->create();

        if (isset($data[BasketPrice::NET]) && is_scalar($data[BasketPrice::NET])) {
            $basketPrice->setNet(DecimalCalculator::round((float)$data[BasketPrice::NET]));
        }

        if (isset($data[BasketPrice::GROSS]) && is_scalar($data[BasketPrice::GROSS])) {
            $basketPrice->setGross(DecimalCalculator::round((float)$data[BasketPrice::GROSS]));
        }

        if (isset($data[BasketPrice::VAT]) && is_scalar($data[BasketPrice::VAT])) {
            $basketPrice->setVat(DecimalCalculator::round((float)$data[BasketPrice::VAT]));
        }

        return $basketPrice;
    }

    private function prepareOrderComments(array $data): string
    {
        $comment = '';
        if (isset($data[OrderDetails::ORDER_COMMENTS]) && is_scalar($data[OrderDetails::ORDER_COMMENTS])) {
            $comment = (string)$data[OrderDetails::ORDER_COMMENTS];
        } elseif (isset($data[OrderDetails::COMMENTS]) && is_scalar($data[OrderDetails::COMMENTS])) {
            $comment = (string)$data[OrderDetails::COMMENTS];
        } elseif (isset($data[OrderDetails::ORDER_COMMENTS]) && is_array($data[OrderDetails::ORDER_COMMENTS])) {
            $orderComments = $data[OrderDetails::ORDER_COMMENTS];
            if (isset($orderComments[OrderDetails::COMMENTS]) && is_scalar($orderComments[OrderDetails::COMMENTS])) {
                $comment = (string)$orderComments[OrderDetails::COMMENTS];
            }
        }

        return $comment;
    }
}
