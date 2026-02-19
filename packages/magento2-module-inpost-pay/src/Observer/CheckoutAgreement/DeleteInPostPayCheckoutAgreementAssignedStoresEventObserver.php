<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\CheckoutAgreement;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use InPost\InPostPay\Api\InPostPayCheckoutAgreementStoreRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Psr\Log\LoggerInterface;

class DeleteInPostPayCheckoutAgreementAssignedStoresEventObserver implements ObserverInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly InPostPayCheckoutAgreementStoreRepositoryInterface $inPostPayCheckoutAgreementStoreRepository
    ) {
    }

    public function execute(Observer $observer): void
    {
        $agreement = $observer->getEvent()->getData(InPostPayCheckoutAgreementInterface::ENTITY_NAME);

        if ($agreement instanceof InPostPayCheckoutAgreementInterface) {
            foreach ($agreement->getAssignedStoreRecords() as $assignedStoreRecord) {
                try {
                    $this->inPostPayCheckoutAgreementStoreRepository->delete($assignedStoreRecord);
                    $this->logger->debug(
                        sprintf(
                            'Agreement ID:%s has been unassigned from store ID:%s',
                            $assignedStoreRecord->getAgreementId(),
                            $assignedStoreRecord->getStoreId()
                        )
                    );
                } catch (CouldNotDeleteException $e) {
                    $this->logger->debug(
                        sprintf(
                            'Agreement ID:%s could not be unassigned from store ID:%s. Reason: %s',
                            $assignedStoreRecord->getAgreementId(),
                            $assignedStoreRecord->getStoreId(),
                            $e->getMessage()
                        )
                    );
                }
            }
        }
    }
}
