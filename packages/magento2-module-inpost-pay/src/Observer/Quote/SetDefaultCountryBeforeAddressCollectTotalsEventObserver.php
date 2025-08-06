<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Quote;

use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Enum\InPostBasketStatus;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use InPost\InPostPay\Service\Cart\ShippingMethod\ShippingMethodEstimator;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class SetDefaultCountryBeforeAddressCollectTotalsEventObserver implements ObserverInterface
{
    /**
     * @param InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository
     * @param ShipmentMappingConfigProvider $shipmentMappingConfigProvider
     */
    public function __construct(
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer): void
    {
        if (!$this->shipmentMappingConfigProvider->isUsingCollectAddressTotalsForShippingEstimationEnabled()) {
            return;
        }

        $quote = $observer->getEvent()->getData('quote');
        $shippingAssignment = $observer->getEvent()->getData('shipping_assignment');

        if ($shippingAssignment instanceof ShippingAssignmentInterface
            && $quote instanceof Quote
            && $this->isBoundInPostPayQuote($quote)
        ) {
            $quoteShippingAddress = $quote->getShippingAddress();

            if (empty($quoteShippingAddress->getCountryId())) {
                $quoteShippingAddress->setCountryId(ShippingMethodEstimator::DEFAULT_COUNTRY_ID);
            }

            $shippingAssignmentAddress = $shippingAssignment->getShipping()->getAddress();

            if (empty($shippingAssignmentAddress->getCountryId())) {
                $shippingAssignmentAddress->setCountryId(ShippingMethodEstimator::DEFAULT_COUNTRY_ID);
            }

            foreach ($shippingAssignment->getItems() as $item) {
                if (!$item instanceof AbstractItem) {
                    continue;
                }

                $itemAddress = $item->getAddress();

                if (empty($itemAddress->getCountryId())) {
                    $itemAddress->setCountryId(ShippingMethodEstimator::DEFAULT_COUNTRY_ID);
                }

                if (empty($itemAddress->getShippingMethod())) {
                    $itemAddress->setData('shipping_method', null);
                }
            }
        }
    }

    /**
     * @param Quote $quote
     * @return bool
     */
    private function isBoundInPostPayQuote(Quote $quote): bool
    {
        $quoteId = (int)(is_scalar($quote->getId()) ? $quote->getId() : null);
        try {
            $inPostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);

            return $inPostPayQuote->getStatus() === InPostBasketStatus::SUCCESS->value;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
}
