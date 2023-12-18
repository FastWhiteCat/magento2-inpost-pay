<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder;

use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\ClientAddressInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\DataTransfer\OrderToInPostOrderDataTransferInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;

class OrderToInPostOrderAccountInfoDataTransfer implements OrderToInPostOrderDataTransferInterface
{
    public function transfer(Order $order, OrderInterface $inPostOrder): void
    {
        $accountInfo = $inPostOrder->getAccountInfo();
        $accountInfo->setMail((string)$order->getCustomerEmail());
        $accountInfo->setName((string)$order->getCustomerFirstname());
        $accountInfo->setSurname((string)$order->getCustomerLastname());
        $billingAddress = $order->getBillingAddress();
        if ($billingAddress) {
            $this->transferPhoneNumber($billingAddress, $accountInfo);
            $this->transferClientAddress($billingAddress, $accountInfo);
        }
        $inPostOrder->setAccountInfo($accountInfo);
    }

    private function transferPhoneNumber(OrderAddressInterface $address, AccountInfoInterface $accountInfo): void
    {
        $prefix = '';
        preg_match('/\+[0-9]{2}/', (string)$address->getTelephone(), $matches);
        if (is_array($matches) && !empty($matches)) {
            $prefix = current($matches);
        }
        $phone = str_replace($prefix, '', (string)$address->getTelephone());
        $phoneNumber = $accountInfo->getPhoneNumber();
        $phoneNumber->setCountryPrefix($prefix);
        $phoneNumber->setPhone($phone);
        $accountInfo->setPhoneNumber($phoneNumber);
    }

    private function transferClientAddress(OrderAddressInterface $address, AccountInfoInterface $accountInfo): void
    {
        $clientAddress = $accountInfo->getClientAddress();
        $this->transferAddressDetails($address, $clientAddress);
        $clientAddress->setCountryCode((string)$address->getCountryId());
        $clientAddress->setCity((string)$address->getCity());
        $clientAddress->setPostalCode((string)$address->getPostcode());
        $accountInfo->setClientAddress($clientAddress);
    }

    private function transferAddressDetails(
        OrderAddressInterface $address,
        ClientAddressInterface $clientAddress
    ): void {
        $streetData = $address->getStreet() ?? [];
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

        $clientAddress->setAddress($addressLine);

        $addressDetails = $clientAddress->getAddressDetails();
        $addressDetails->setStreet($street);
        $addressDetails->setBuilding($building);
        $addressDetails->setFlat($flat);

        $clientAddress->setAddressDetails($addressDetails);
    }
}
