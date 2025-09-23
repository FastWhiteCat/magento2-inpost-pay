<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder;

use InPost\InPostPay\Enum\InPostDeliveryType;
use InPost\InPostPay\Provider\Delivery\DeliveryDateProvider;
use \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory;
use \Magento\Quote\Api\Data\ShippingMethodInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\Delivery\DeliveryOptionInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderToInPostOrderDeliveryDataTransfer implements OrderToInPostOrderDataTransferInterface
{
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly ShippingMethodInterfaceFactory $shippingMethodInterfaceFactory,
        private readonly DeliveryDateProvider $deliveryDateProvider,
        private readonly DeliveryOptionInterfaceFactory $deliveryOptionFactory
    ) {
    }

    public function transfer(Order $order, OrderInterface $inPostOrder): void
    {
        $delivery = $inPostOrder->getDelivery();
        $orderShippingMethodCode = $this->getOrderShippingMethodCode($order);

        if ($order->getIsVirtual()) {
            $this->appendDigitalDeliveryData($order, $delivery);
            $inPostOrder->setDelivery($delivery);

            return;
        }

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
                    /** @var ShippingMethodInterface $shippingMethod */
                    $shippingMethod = $this->shippingMethodInterfaceFactory->create();
                    $shippingMethod->setMethodCode($configMethodCode);
                    $delivery->setDeliveryDate($this->deliveryDateProvider->calculateDeliveryDate($shippingMethod));

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
            $this->shipmentMappingConfigProvider->getNonStandardDeliveryOptions(true),
            [ShipmentMappingConfigProvider::OPTION_STANDARD]
        );
    }

    private function appendDeliveryData(
        Order $order,
        DeliveryInterface $delivery,
        string $deliveryType,
        string $deliveryOptionCode
    ): void {
        $orderId = (is_scalar($order->getId())) ? (int)$order->getId() : 0;
        $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId($orderId);
        $orderShippingAddress = $order->getShippingAddress();
        $shippingPriceInclTax = DecimalCalculator::round(
            DecimalCalculator::sub(
                (float)$order->getShippingInclTax(),
                (float)$order->getShippingDiscountAmount()
            )
        );

        $shippingPriceTax = DecimalCalculator::round((float)$order->getShippingTaxAmount());
        $shippingPriceExclTax = DecimalCalculator::sub($shippingPriceInclTax, $shippingPriceTax);
        $delivery->setDeliveryType($deliveryType);
        $delivery->setDeliveryCodes($inPostPayOrder->getDeliveryOptions());

        $deliveryPrice = $delivery->getDeliveryPrice();
        if ($deliveryPrice instanceof PriceInterface) {
            $deliveryPrice->setNet($shippingPriceExclTax);
            $deliveryPrice->setGross($shippingPriceInclTax);
            $deliveryPrice->setVat($shippingPriceTax);
            $delivery->setDeliveryPrice($deliveryPrice);
        }

        $delivery->setMail($order->getCustomerEmail());
        $delivery->setPhoneNumber($inPostPayOrder->getPhoneNumber());

        if ($inPostPayOrder->getDigitalDeliveryEmail()) {
            $delivery->setDigitalDeliveryEmail($inPostPayOrder->getDigitalDeliveryEmail());
        }

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

    private function appendDigitalDeliveryData(Order $order, DeliveryInterface $delivery): void
    {
        $orderId = (is_scalar($order->getId())) ? (int)$order->getId() : 0;
        $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId($orderId);
        $delivery->setDeliveryType(InPostDeliveryType::DIGITAL->value);
        $orderBillingAddress = $order->getBillingAddress();

        $deliveryPrice = $delivery->getDeliveryPrice();
        if ($deliveryPrice instanceof PriceInterface) {
            $deliveryPrice->setNet(0);
            $deliveryPrice->setGross(0);
            $deliveryPrice->setVat(0);
            $delivery->setDeliveryPrice($deliveryPrice);
        }

        $delivery->setMail($order->getCustomerEmail());
        $delivery->setDigitalDeliveryEmail($inPostPayOrder->getDigitalDeliveryEmail());
        $delivery->setPhoneNumber($inPostPayOrder->getPhoneNumber());

        if ($orderBillingAddress instanceof Address) {
            $this->appendDeliveryAddressData($orderBillingAddress, $delivery);
        }
    }

    private function appendDeliveryAddressData(Address $orderAddress, DeliveryInterface $delivery): void
    {
        $deliveryAddress = $delivery->getDeliveryAddress();
        $deliveryAddress->setName(
            sprintf('%s %s', $orderAddress->getFirstname(), $orderAddress->getLastname())
        );

        $streetData = $orderAddress->getStreet();
        $street = (isset($streetData[0])) ? (string)$streetData[0] : '';
        $building = (isset($streetData[1])) ? (string)$streetData[1] : '';
        $flat = (isset($streetData[2])) ? (string)$streetData[2] : '';

        $addressLine = $street;
        if ($building) {
            $addressLine = sprintf('%s %s', $addressLine, $building);
        }

        if ($flat) {
            $addressLine = sprintf('%s/%s', $addressLine, $flat);
        }

        $deliveryAddress->setAddress($addressLine);
        $deliveryAddress->setCity($orderAddress->getCity());
        $deliveryAddress->setPostalCode($orderAddress->getPostcode());
        $deliveryAddress->setCountryCode($orderAddress->getCountryId());

        $addressDetails = $deliveryAddress->getAddressDetails();
        $addressDetails->setStreet($street);
        $addressDetails->setBuilding($building);
        $addressDetails->setFlat($flat);
        $deliveryAddress->setAddressDetails($addressDetails);

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
