<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder;

use InPost\InPostPay\Api\Data\Merchant\Order\AccountInfoInterface;
use InPost\InPostPay\Api\Data\Merchant\Order\ClientAddressInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\DataTransfer\OrderToInPostOrderDataTransferInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Exception\InPostPayException;
use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;

class OrderToInPostOrderAccountInfoDataTransfer implements OrderToInPostOrderDataTransferInterface
{
    /**
     * @param GeneralConfigProvider $generalConfigProvider
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     */
    public function __construct(
        private readonly GeneralConfigProvider $generalConfigProvider,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository
    ) {
    }

    public function transfer(Order $order, OrderInterface $inPostOrder): void
    {
        $accountInfo = $inPostOrder->getAccountInfo();
        $accountInfo->setMail((string)$order->getCustomerEmail());
        $firstname = $this->extractFirstnameFromOrder($order);
        $lastname = $this->extractLastnameFromOrder($order);
        $accountInfo->setName($firstname);
        $accountInfo->setSurname($lastname);
        $billingAddress = $order->getBillingAddress();
        if ($billingAddress) {
            $this->transferPhoneNumber($billingAddress, $accountInfo);
            $this->transferClientAddress($billingAddress, $accountInfo);
        }

        $inPostPayAccountEmail = $this->getInPostPayAccountEmailByOrder($order);
        if ($inPostPayAccountEmail) {
            $accountInfo->setMail($inPostPayAccountEmail);
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

    /**
     * @param Order $order
     * @return string
     * @throws InPostPayException
     */
    private function extractFirstnameFromOrder(Order $order): string
    {
        $firstname = (string)$order->getCustomerFirstname();

        if (!empty($firstname) && !$this->generalConfigProvider->isUsingAddressAsDataSourceEnabled()) {
            return $firstname;
        }

        foreach ($order->getAddresses() as $orderAddress) {
            if (!empty($orderAddress->getFirstname())) {
                $firstname = $orderAddress->getFirstname();
            }
        }

        if (empty($firstname)) {
            throw new InPostPayException(
                __('Failed to retrieve a non-empty customer firstname from order and order addresses.')
            );
        }

        return $firstname;
    }

    /**
     * @param Order $order
     * @return string
     * @throws InPostPayException
     */
    private function extractLastnameFromOrder(Order $order): string
    {
        $lastname = (string)$order->getCustomerLastname();

        if (!empty($lastname) && !$this->generalConfigProvider->isUsingAddressAsDataSourceEnabled()) {
            return $lastname;
        }

        foreach ($order->getAddresses() as $orderAddress) {
            if (!empty($orderAddress->getLastname())) {
                $lastname = $orderAddress->getLastname();
            }
        }

        if (empty($lastname)) {
            throw new InPostPayException(
                __('Failed to retrieve a non-empty customer lastname from order and order addresses.')
            );
        }

        return $lastname;
    }

    private function getInPostPayAccountEmailByOrder(Order $order): ?string
    {
        $orderId = (is_scalar($order->getId())) ? (int)$order->getId() : 0;

        try {
            $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId($orderId);
            $inPostPayAccountEmail = $inPostPayOrder->getInPostPayAccountEmail();
        } catch (NoSuchEntityException $e) {
            $inPostPayAccountEmail = null;
        }

        return $inPostPayAccountEmail;
    }
}
