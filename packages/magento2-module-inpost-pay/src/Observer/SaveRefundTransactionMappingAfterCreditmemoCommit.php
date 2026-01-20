<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer;

use Exception;
use InPost\InPostPay\Api\Data\RefundTransactionInterface;
use InPost\InPostPay\Api\RefundTransactionRepositoryInterface;
use InPost\InPostPay\Model\RefundTransactionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Psr\Log\LoggerInterface;

class SaveRefundTransactionMappingAfterCreditmemoCommit implements ObserverInterface
{
    public function __construct(
        private readonly RefundTransactionRepositoryInterface $repository,
        private readonly RefundTransactionFactory $refundTransactionFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(Observer $observer): void
    {
        $creditmemo = $observer->getData('creditmemo');

        if (!$creditmemo instanceof CreditmemoInterface) {
            return;
        }

        $creditmemoId = $creditmemo->getEntityId();
        $externalId = $creditmemo->getExtensionAttributes()?->getInpostPayRefundExternalTransactionId();

        if (!is_scalar($creditmemoId) || !$externalId || !is_scalar($externalId)) {
            return;
        }

        $creditmemoId = (int)$creditmemoId;
        $externalId = (string)$externalId;

        try {
            $this->repository->getByExternalTransactionId($externalId);

            return;
        } catch (NoSuchEntityException $e) {
            /** @var RefundTransactionInterface $mapping */
            $mapping = $this->refundTransactionFactory->create();
        }

        $mapping->setCreditmemoId($creditmemoId);
        $mapping->setRefundExternalTransactionId($externalId);

        try {
            $this->repository->save($mapping);
            $this->logger->debug(
                'InPost Pay Refund transaction mapping successfully saved.',
                [
                    RefundTransactionInterface::CREDITMEMO_ID => $creditmemoId,
                    RefundTransactionInterface::REFUND_EXTERNAL_TRANSACTION_ID => $externalId
                ]
            );
        } catch (Exception $e) {
            $this->logger->critical(
                sprintf(
                    'InPost Pay Refund transaction mapping could not be saved. Reason: %s',
                    $e->getMessage()
                ),
                [
                    RefundTransactionInterface::CREDITMEMO_ID => $creditmemoId,
                    RefundTransactionInterface::REFUND_EXTERNAL_TRANSACTION_ID => $externalId
                ]
            );
        }
    }
}
