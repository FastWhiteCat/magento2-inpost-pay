<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Refund;

use InPost\InPostPay\Api\Data\Merchant\Refund\EventData\AmountInterface;
use InPost\InPostPay\Api\Data\Merchant\Refund\EventData\AmountInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Refund\EventData\PaymentInterface;
use InPost\InPostPay\Api\Data\Merchant\Refund\EventData\PaymentInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Refund\EventDataInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class EventData extends DataObject implements EventDataInterface, ExtensibleDataInterface
{
    /**
     * @param AmountInterfaceFactory $amountFactory
     * @param PaymentInterfaceFactory $paymentFactory
     * @param array $data
     */
    public function __construct(
        private readonly AmountInterfaceFactory $amountFactory,
        private readonly PaymentInterfaceFactory $paymentFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return string|null
     */
    public function getOperationId(): ?string
    {
        $operationId = $this->getData(self::OPERATION_ID);

        return (is_scalar($operationId)) ? (string)$operationId : null;
    }

    /**
     * @param string|null $operationId
     * @return void
     */
    public function setOperationId(?string $operationId): void
    {
        $this->setData(self::OPERATION_ID, $operationId);
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        $status = $this->getData(self::STATUS);

        return (is_scalar($status)) ? (string)$status : null;
    }

    /**
     * @param string|null $status
     * @return void
     */
    public function setStatus(?string $status): void
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @return AmountInterface
     */
    public function getAmount(): AmountInterface
    {
        $amount = $this->getData(self::AMOUNT);

        if ($amount instanceof AmountInterface) {
            return $amount;
        }

        return $this->amountFactory->create();
    }

    /**
     * @param AmountInterface $amount
     * @return void
     */
    public function setAmount(AmountInterface $amount): void
    {
        $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @return PaymentInterface
     */
    public function getPayment(): PaymentInterface
    {
        $payment = $this->getData(self::PAYMENT);

        if ($payment instanceof PaymentInterface) {
            return $payment;
        }

        return $this->paymentFactory->create();
    }

    /**
     * @param PaymentInterface $payment
     * @return void
     */
    public function setPayment(PaymentInterface $payment): void
    {
        $this->setData(self::PAYMENT, $payment);
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        $merchantId = $this->getData(self::MERCHANT_ID);

        return (is_scalar($merchantId)) ? (string)$merchantId : '';
    }

    /**
     * @param string $merchantId
     * @return void
     */
    public function setMerchantId(string $merchantId): void
    {
        $this->setData(self::MERCHANT_ID, $merchantId);
    }

    /**
     * @return string
     */
    public function getEventDateTime(): string
    {
        $eventDateTime = $this->getData(self::EVENT_DATE_TIME);

        return (is_scalar($eventDateTime)) ? (string)$eventDateTime : '';
    }

    /**
     * @param string $eventDateTime
     * @return void
     */
    public function setEventDateTime(string $eventDateTime): void
    {
        $this->setData(self::EVENT_DATE_TIME, $eventDateTime);
    }

    /**
     * @return string
     */
    public function getCreatedDate(): string
    {
        $createdDate = $this->getData(self::CREATED_DATE);

        return (is_scalar($createdDate)) ? (string)$createdDate : '';
    }

    /**
     * @param string $createdDate
     * @return void
     */
    public function setCreatedDate(string $createdDate): void
    {
        $this->setData(self::CREATED_DATE, $createdDate);
    }

    /**
     * @return string|null
     */
    public function getRefundReference(): ?string
    {
        $refundReference = $this->getData(self::REFUND_REFERENCE);

        return (is_scalar($refundReference)) ? (string)$refundReference : null;
    }

    /**
     * @param string|null $refundReference
     * @return void
     */
    public function setRefundReference(?string $refundReference): void
    {
        $this->setData(self::REFUND_REFERENCE, $refundReference);
    }
}
