<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterfaceFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class InPostPayOrder extends AbstractModel implements InPostPayOrderInterface
{
    private const DELIVERY_OPTIONS_SEPARATOR = ',';

    protected $_eventPrefix = InPostPayOrderInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayOrderInterface::ENTITY_NAME;

    private ?PhoneNumberInterface $phoneNumber;

    public function __construct(
        Context $context,
        Registry $registry,
        private readonly PhoneNumberInterfaceFactory $phoneNumberInterfaceFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function _construct(): void
    {
        $this->_init(ResourceModel\InPostPayOrder::class);
    }

    public function getInPostPayOrderId(): ?int
    {
        $id = ($this->hasData(self::INPOST_PAY_ORDER_ID)) ? $this->getData(self::INPOST_PAY_ORDER_ID) : null;

        return ($id && is_scalar($id)) ? (int)$id : null;
    }

    public function setInPostPayOrderId(int $inPostPayOrderId): InPostPayOrderInterface
    {
        return $this->setData(self::INPOST_PAY_ORDER_ID, $inPostPayOrderId);
    }

    public function getOrderId(): int
    {
        $orderId = $this->getData(self::ORDER_ID);

        if ($orderId && is_scalar($orderId)) {
            return (int)$orderId;
        }

        throw new LocalizedException(__('Invalid InPost Pay Order ID value.'));
    }

    public function setOrderId(int $orderId): InPostPayOrderInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    public function getBasketId(): ?string
    {
        $basketId = $this->getData(self::BASKET_ID);

        return (is_scalar($basketId)) ? (string)$basketId : null;
    }

    public function setBasketId(?string $basketId): InPostPayOrderInterface
    {
        return $this->setData(self::BASKET_ID, $basketId);
    }

    public function getPaymentType(): ?string
    {
        $paymentType = $this->getData(self::PAYMENT_TYPE);

        return (is_scalar($paymentType)) ? (string)$paymentType : null;
    }

    public function setPaymentType(?string $paymentType): InPostPayOrderInterface
    {
        return $this->setData(self::PAYMENT_TYPE, $paymentType);
    }

    public function getLockerId(): ?string
    {
        $lockerId = ($this->hasData(self::LOCKER_ID)) ? $this->getData(self::LOCKER_ID) : null;

        return ($lockerId && is_scalar($lockerId)) ? (string)$lockerId : null;
    }

    public function setLockerId(string $lockerId): InPostPayOrderInterface
    {
        return $this->setData(self::LOCKER_ID, $lockerId);
    }

    public function getDeliveryOptions(): array
    {
        $deliveryOptions = $this->getData(self::DELIVERY_OPTIONS);
        if (!empty($deliveryOptions) && is_scalar($deliveryOptions)) {
            return explode(self::DELIVERY_OPTIONS_SEPARATOR, (string)$deliveryOptions);
        }

        return [];
    }

    public function setDeliveryOptions(array $deliveryOptions): InPostPayOrderInterface
    {
        return $this->setData(self::DELIVERY_OPTIONS, implode(self::DELIVERY_OPTIONS_SEPARATOR, $deliveryOptions));
    }

    public function getOrderStatus(): ?string
    {
        $orderStatus = ($this->hasData(self::ORDER_STATUS)) ? $this->getData(self::ORDER_STATUS) : null;

        return ($orderStatus && is_scalar($orderStatus)) ? (string)$orderStatus : null;
    }

    public function setOrderStatus(string $orderStatus): InPostPayOrderInterface
    {
        return $this->setData(self::ORDER_STATUS, $orderStatus);
    }

    public function getPhone(): ?string
    {
        $phone = ($this->hasData(self::PHONE)) ? $this->getData(self::PHONE) : null;

        return ($phone && is_scalar($phone)) ? (string)$phone : null;
    }

    public function setPhone(string $phone): InPostPayOrderInterface
    {
        return $this->setData(self::PHONE, $phone);
    }

    public function getCountryPrefix(): ?string
    {
        $countryPrefix = ($this->hasData(self::COUNTRY_PREFIX)) ? $this->getData(self::COUNTRY_PREFIX) : null;

        return ($countryPrefix && is_scalar($countryPrefix)) ? (string)$countryPrefix : null;
    }

    public function setCountryPrefix(string $countryPrefix): InPostPayOrderInterface
    {
        return $this->setData(self::COUNTRY_PREFIX, $countryPrefix);
    }

    public function getPhoneNumber(): PhoneNumberInterface
    {
        if (!$this->phoneNumber) {
            $this->phoneNumber = $this->phoneNumberInterfaceFactory->create();
        }

        $this->phoneNumber->setPhone((string)$this->getPhone());
        $this->phoneNumber->setCountryPrefix((string)$this->getCountryPrefix());

        return $this->phoneNumber;
    }

    public function getCreatedAt(): string
    {
        $createdAt = $this->getData(self::CREATED_AT);

        if ($createdAt && is_scalar($createdAt)) {
            return (string)$createdAt;
        }

        throw new LocalizedException(__('Invalid InPost Pay Order created at value.'));
    }

    public function getUpdatedAt(): string
    {
        $updatedAt = $this->getData(self::UPDATED_AT);

        if ($updatedAt && is_scalar($updatedAt)) {
            return (string)$updatedAt;
        }

        throw new LocalizedException(__('Invalid InPost Pay Order updated at value.'));
    }
}
