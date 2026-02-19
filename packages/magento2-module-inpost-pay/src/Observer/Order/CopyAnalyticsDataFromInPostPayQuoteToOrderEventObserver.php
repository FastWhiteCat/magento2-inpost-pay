<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Order;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Provider\Config\AnalyticsConfigProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class CopyAnalyticsDataFromInPostPayQuoteToOrderEventObserver implements ObserverInterface
{
    /**
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     * @param AnalyticsConfigProvider $analyticsConfigProvider
     */
    public function __construct(
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly AnalyticsConfigProvider $analyticsConfigProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getData('order');
        $inPostPayQuote = $observer->getEvent()->getData('inpost_pay_quote');

        if ($order instanceof Order
            && $inPostPayQuote instanceof InPostPayQuoteInterface
            && $this->analyticsConfigProvider->isAnalyticsEnabled((int)$order->getStoreId())
        ) {
            try {
                $inPostPayOrder = $this->inPostPayOrderRepository->getByBasketId($inPostPayQuote->getBasketId());
                $inPostPayOrder->setGaClientId($inPostPayQuote->getGaClientId());
                $inPostPayOrder->setFbclid($inPostPayQuote->getFbclid());
                $inPostPayOrder->setGclid($inPostPayQuote->getGclid());
                $this->inPostPayOrderRepository->save($inPostPayOrder);
            } catch (NoSuchEntityException $e) {
                $this->logger->error(
                    sprintf('InPost Pay Order not found by basket ID: %s.', $inPostPayQuote->getBasketId())
                );
            } catch (CouldNotSaveException $e) {
                $this->logger->error(
                    sprintf(
                        'Could not save InPost Pay Order [Basket ID: %s] analytics params. Reason: %s.',
                        $inPostPayQuote->getBasketId(),
                        $e->getMessage()
                    )
                );
            }
        }
    }
}
