<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Refund\AdditionalBusinessDataInterface;
use InPost\InPostPay\Model\Data\Merchant\Refund\AdditionalBusinessDataFactory;
use InPost\InPostPay\Api\Data\Merchant\RefundInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class Refund extends DataObject implements RefundInterface, ExtensibleDataInterface
{
    public function __construct(
        private readonly AdditionalBusinessDataFactory $additionalBusinessDataFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    public function getTransactionId(): ?string
    {
        $transactionId = $this->getData(self::TRANSACTION_ID);

        return is_scalar($transactionId) ? (string)$transactionId : null;
    }

    public function setTransactionId(?string $transactionId): void
    {
        $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    public function getExternalRefundId(): ?string
    {
        $externalRefundId = $this->getData(self::EXTERNAL_REFUND_ID);

        return is_scalar($externalRefundId) ? (string)$externalRefundId : null;
    }

    public function setExternalRefundId(?string $externalRefundId): void
    {
        $this->setData(self::EXTERNAL_REFUND_ID, $externalRefundId);
    }

    public function getRefundAmount(): ?float
    {
        $refundAmount = $this->getData(self::REFUND_AMOUNT);

        return is_scalar($refundAmount) ? (float)$refundAmount : null;
    }

    public function setRefundAmount(?float $refundAmount): void
    {
        $this->setData(self::REFUND_AMOUNT, $refundAmount);
    }

    public function getAdditionalBusinessData(): AdditionalBusinessDataInterface
    {
        $additionalBusinessData = $this->getData(self::ADDITIONAL_BUSINESS_DATA);

        if ($additionalBusinessData instanceof AdditionalBusinessDataInterface) {
            return $additionalBusinessData;
        }

        return $this->additionalBusinessDataFactory->create();
    }

    public function setAdditionalBusinessData(AdditionalBusinessDataInterface $additionalBusinessData): void
    {
        $this->setData(self::ADDITIONAL_BUSINESS_DATA, $additionalBusinessData);
    }
}
