<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder;

use InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\DeliveryInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\DataTransfer\OrderToInPostOrderDataTransferInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use InPost\InPostPay\Service\Calculator\DecimalCalculator;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use phpseclib3\Math\PrimeField;

class OrderToInPostOrderDeliveryDataTransfer implements OrderToInPostOrderDataTransferInterface
{
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly DeliveryOptionInterfaceFactory $deliveryOptionFactory
    ) {
    }

    public function transfer(Order $order, OrderInterface $inPostOrder): void
    {
        $delivery = $inPostOrder->getDelivery();
        $orderShippingMethodCode = $this->getOrderShippingMethodCode($order);

        foreach ($this->shipmentMappingConfigProvider->getAllDeliveryTypes() as $deliveryType) {
            foreach ($this->getAllDeliveryOptions() as $deliveryOptionCode) {
                try {
                    $configMethodCode = $this->shipmentMappingConfigProvider->getCarrierMethodCodeForOptions(
                        $deliveryType,
                        $deliveryOptionCode
                    );
                } catch (InPostPayInternalException $e) {
                    continue;
                }

                if ($orderShippingMethodCode === $configMethodCode) {
                    $this->appendDeliveryData($order, $delivery, $deliveryType, $deliveryOptionCode);
                    break 2;
                }
            }
        }


        $inPostOrder->setDelivery($delivery);
    }

    /**
     * @return string[]
     */
    public function getAllDeliveryOptions(): array
    {
        return array_merge(
            $this->shipmentMappingConfigProvider->getNonStandardDeliveryOptions(),
            [ShipmentMappingConfigProvider::OPTION_STANDARD]
        );
    }

    private function appendDeliveryData(
        Order $order,
        DeliveryInterface $delivery,
        string $deliveryType,
        string $deliveryOptionCode
    ) {
        $orderId = (is_scalar($order->getId())) ? (int)$order->getId() : 0;
        $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId($orderId);
        $orderShippingAddress = $order->getShippingAddress();
        $shippingPriceInclTax = DecimalCalculator::round((float)$order->getShippingInclTax());
        $shippingPriceTax = DecimalCalculator::round((float)$order->getShippingTaxAmount());
        $shippingPriceExclTax = DecimalCalculator::sub($shippingPriceInclTax, $shippingPriceTax);
        $deliveryPrice = $delivery->getDeliveryPrice();

        $delivery->setDeliveryType($deliveryType);
        $delivery->setDeliveryCodes($inPostPayOrder->getDeliveryOptions());
        $deliveryPrice->setNet($shippingPriceExclTax);
        $deliveryPrice->setGross($shippingPriceInclTax);
        $deliveryPrice->setVat($shippingPriceTax);
        $delivery->setDeliveryPrice($deliveryPrice);
        $delivery->setMail($order->getCustomerEmail());
        $delivery->setPhoneNumber($inPostPayOrder->getPhoneNumber());

        if ($inPostPayOrder->getCourierNote()) {
            $delivery->setCourierNote($inPostPayOrder->getCourierNote());
        }

        if ($orderShippingAddress instanceof Address) {
            $this->appendDeliveryAddressData($orderShippingAddress, $delivery);
        }

        if ($inPostPayOrder->getLockerId()) {
            $delivery->setDeliveryPoint($inPostPayOrder->getLockerId());
        }

        if ($deliveryOptionCode !== ShipmentMappingConfigProvider::OPTION_STANDARD) {
            $this->appendDeliveryOptionData($order, $deliveryOptionCode, $delivery);
        }
    }

    private function appendDeliveryAddressData(Address $orderShippingAddress, DeliveryInterface $delivery): void
    {
        $deliveryAddress = $delivery->getDeliveryAddress();
        $streetData = $orderShippingAddress->getStreet() ?? [];
        $addressLine = implode(PHP_EOL, $streetData);
        $deliveryAddress->setName(
            sprintf('%s %s', $orderShippingAddress->getFirstname(), $orderShippingAddress->getLastname())
        );
        $deliveryAddress->setAddress($addressLine);
        $deliveryAddress->setCity($orderShippingAddress->getCity());
        $deliveryAddress->setPostalCode($orderShippingAddress->getPostcode());
        $deliveryAddress->setCountryCode($orderShippingAddress->getCountryId());
        $delivery->setDeliveryAddress($deliveryAddress);
    }

    private function appendDeliveryOptionData(
        Order $order,
        string $deliveryOptionCode,
        DeliveryInterface $delivery
    ): void {
        /** @var DeliveryOptionInterface $deliveryOption */
        $deliveryOption = $this->deliveryOptionFactory->create();
        $deliveryOption->setDeliveryCodeValue($deliveryOptionCode);
        $deliveryOption->setDeliveryName((string)$order->getShippingDescription());
        $deliveryOptionPrice = $deliveryOption->getDeliveryOptionPrice();
        $deliveryOptionPrice->setNet(0);
        $deliveryOptionPrice->setGross(0);
        $deliveryOptionPrice->setVat(0);
        $deliveryOption->setDeliveryOptionPrice($deliveryOptionPrice);
        $delivery->setDeliveryOptions([$deliveryOption]);
    }

    private function getOrderShippingMethodCode(Order $order): string
    {
        $methodCode = '';
        $shippingMethod = $order->getShippingMethod();
        if (is_string($shippingMethod)) {
            $methodCode = $shippingMethod;
        }

        if ($shippingMethod instanceof DataObject) {
            // @phpstan-ignore-next-line
            $carrierCode = $shippingMethod->getCarrierCode();
            // @phpstan-ignore-next-line
            $methodCode = $shippingMethod->getMethodCode();

            $methodCode = sprintf('%s_%s', $carrierCode, $methodCode);
        }

        return $methodCode;
    }
}
