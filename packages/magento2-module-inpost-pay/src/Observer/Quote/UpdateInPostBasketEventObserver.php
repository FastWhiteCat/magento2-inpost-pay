<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Quote;

use InPost\InPostPay\Service\ApiConnector\CreateOrUpdateBasket;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class UpdateInPostBasketEventObserver implements ObserverInterface
{
    public const SKIP_INPOST_PAY_SYNC_FLAG = 'skip_inpost_pay_sync';

    public function __construct(
        private readonly CreateOrUpdateBasket $createOrUpdateBasket,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $quote = $observer->getEvent()->getData('quote');
        if ($quote instanceof Quote && $this->canSync($quote)) {
            //TODO:: browser_id and basket_id in INPAY-28
            $browserId = '2d387d15-d4fe-43f8-85dc-32d46cfc3b53';
            $basketId = 'eBhUFKsJjFQWRYWBSRAEtymGPy2ed5X9';
            try {
                $this->createOrUpdateBasket->execute($quote, $browserId, $basketId);
            } catch (LocalizedException $e) {
                $errorMsg = 'Basket synchronization with InPost Pay was not successful.';
                $this->logger->error(sprintf('%s Reason: %s', $errorMsg, $e->getMessage()));
            }
        }
    }

    private function canSync(Quote $quote): bool
    {
        //TODO:: validate if quote is in inpost_pay_quote table and has browser id.

        if ($quote->getData(self::SKIP_INPOST_PAY_SYNC_FLAG)) {
            return false;
        }

        return true;
    }
}
