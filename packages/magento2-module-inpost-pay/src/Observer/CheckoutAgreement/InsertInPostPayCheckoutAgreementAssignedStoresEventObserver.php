<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\CheckoutAgreement;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementStoreInterface;
use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementStoreInterfaceFactory as AssignedStoreRecordFactory;
use InPost\InPostPay\Api\InPostPayCheckoutAgreementStoreRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class InsertInPostPayCheckoutAgreementAssignedStoresEventObserver implements ObserverInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly AssignedStoreRecordFactory $assignedStoreRecordFactory,
        private readonly InPostPayCheckoutAgreementStoreRepositoryInterface $inPostPayCheckoutAgreementStoreRepository
    ) {
    }

    public function execute(Observer $observer): void
    {
        $agreement = $observer->getEvent()->getData(InPostPayCheckoutAgreementInterface::ENTITY_NAME);

        if ($agreement instanceof InPostPayCheckoutAgreementInterface) {
            foreach ($agreement->getStoreIds() as $storeId) {
                /** @var InPostPayCheckoutAgreementStoreInterface $assignedStoreRecord */
                $assignedStoreRecord = $this->assignedStoreRecordFactory->create();
                $assignedStoreRecord->setAgreementId((int)$agreement->getAgreementId());
                $assignedStoreRecord->setStoreId((int)$storeId);
                try {
                    $this->inPostPayCheckoutAgreementStoreRepository->save($assignedStoreRecord);
                    $this->logger->debug(
                        sprintf(
                            'Agreement ID:%s has been assigned to store ID:%s',
                            $assignedStoreRecord->getAgreementId(),
                            $assignedStoreRecord->getStoreId()
                        )
                    );
                } catch (CouldNotSaveException $e) {
                    $this->logger->debug(
                        sprintf(
                            'Agreement ID:%s could not be assigned to store ID:%s. Reason: %s',
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
