<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Refund\AdditionalBusinessDataInterface;

interface RefundInterface
{
    public const X_COMMAND_ID = 'X-Command-ID';
    public const TRANSACTION_ID = 'transaction_id';
    public const EXTERNAL_REFUND_ID = 'external_refund_id';
    public const REFUND_AMOUNT = 'refund_amount';
    public const ADDITIONAL_BUSINESS_DATA = 'additional_business_data';
    public const SIGNATURE = 'signature';

    /**
     * @return string
     */
    public function getXCommandId(): string;

    /**
     * @param string $xCommandId
     * @return void
     */
    public function setXCommandId(string $xCommandId): void;

    /**
     * @return string|null
     */
    public function getTransactionId(): ?string;

    /**
     * @param string|null $transactionId
     * @return void
     */
    public function setTransactionId(string|null $transactionId): void;

    /**
     * @return string|null
     */
    public function getExternalRefundId(): ?string;

    /**
     * @param string|null $externalRefundId
     * @return void
     */
    public function setExternalRefundId(string|null $externalRefundId): void;

    /**
     * @return null|float
     */
    public function getRefundAmount(): ?float;

    /**
     * @param float|null $refundAmount
     * @return void
     */
    public function setRefundAmount(float|null $refundAmount): void;

    /**
     * @return string
     */
    public function getSignature(): string;

    /**
     * @param string $signature
     * @return void
     */
    public function setSignature(string $signature): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Refund\AdditionalBusinessDataInterface
     */
    public function getAdditionalBusinessData(): ?AdditionalBusinessDataInterface;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Refund\AdditionalBusinessDataInterface $additionalBusinessData
     * @return void
     */
    public function setAdditionalBusinessData(?AdditionalBusinessDataInterface $additionalBusinessData): void;
}
