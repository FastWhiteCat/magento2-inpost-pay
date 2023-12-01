<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order;

use InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order\BasketPriceFactory;
use InPost\InPostPay\Service\ApiConnector\Merchant\Dto\Order\OrderDetailsFactory;
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
}
