<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use InPost\InPostPay\Api\Data\RefundTransactionInterface;
use Magento\Framework\Model\AbstractModel;

class RefundTransaction extends AbstractModel implements RefundTransactionInterface
{
    protected $_eventPrefix = RefundTransactionInterface::ENTITY_NAME;
    protected $_eventObject = RefundTransactionInterface::ENTITY_NAME;

    protected function _construct(): void
    {
        $this->_init(ResourceModel\RefundTransaction::class);
    }

    public function getMappingId(): int
    {
        $mappingId = $this->getData(self::MAPPING_ID);

        return is_scalar($mappingId) ? (int)$mappingId : 0;
    }

    public function setMappingId(int $mappingId): void
    {
        $this->setData(self::MAPPING_ID, $mappingId);
    }

    public function getCreditmemoId(): int
    {
        $creditmemoId = $this->getData(self::CREDITMEMO_ID);

        return is_scalar($creditmemoId) ? (int)$creditmemoId : 0;
    }

    public function setCreditmemoId(int $creditmemoId): void
    {
        $this->setData(self::CREDITMEMO_ID, $creditmemoId);
    }

    public function getRefundExternalTransactionId(): string
    {
        $externalTransactionId = $this->getData(self::REFUND_EXTERNAL_TRANSACTION_ID);

        return is_scalar($externalTransactionId) ? (string)$externalTransactionId : '';
    }

    public function setRefundExternalTransactionId(string $externalTransactionId): void
    {
        $this->setData(self::REFUND_EXTERNAL_TRANSACTION_ID, $externalTransactionId);
    }
}
