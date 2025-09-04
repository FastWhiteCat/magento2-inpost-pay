<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\InPostPayBestsellerProduct;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Service\BestsellerProduct\Delete;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;

class InPostPayBestsellerProductAfterDeleteObserver implements ObserverInterface
{
    /**
     * @param Delete $delete
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        private readonly Delete $delete,
        private readonly ManagerInterface $messageManager,
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $bestsellerProduct = $observer->getEvent()->getData(InPostPayBestsellerProductInterface::ENTITY_NAME);

        if ($bestsellerProduct instanceof InPostPayBestsellerProductInterface
            && !$bestsellerProduct->isSkipUpdateFlag()
        ) {
            try {
                $this->delete->execute($bestsellerProduct);
            } catch (LocalizedException $e) {
                $this->messageManager->addWarningMessage(
                    __(
                        'Bestsellers deleted from Admin Panel but could not be deleted in InPost Pay. Reason: %1',
                        $e->getMessage()
                    )->render()
                );
            }
        }
    }
}
