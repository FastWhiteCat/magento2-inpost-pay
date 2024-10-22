<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\InPostPayBestsellerProduct;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Service\BestsellerProduct\Delete;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class InPostPayBestsellerProductAfterDeleteObserver implements ObserverInterface
{
    /**
     * @param Delete $delete
     */
    public function __construct(
        private readonly Delete $delete
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $bestsellerProduct = $observer->getEvent()->getData(InPostPayBestsellerProductInterface::ENTITY_NAME);

        if ($bestsellerProduct instanceof InPostPayBestsellerProductInterface
            && !$bestsellerProduct->isSkipUpdateFlag()
        ) {
            $this->delete->execute($bestsellerProduct);
        }
    }
}
