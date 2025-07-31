<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Cart\ShippingMethod;

use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Quote;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote\Address;
use Psr\Log\LoggerInterface;

class ShippingMethodEstimator
{
    public const DEFAULT_COUNTRY_ID = 'PL';

    public function __construct(
        private readonly AddressRepositoryInterface $addressRepository,
        private readonly ShippingMethodManagementInterface $shippingManager,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly ShippingMethodConverter $shippingMethodConverter,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Quote $quote
     * @return ShippingMethodInterface[]
     */
    public function estimate(Quote $quote): array
    {
        /** @var Address $shippingAddress */
        $shippingAddress = $this->getShippingAddress($quote);
        $quoteId = is_scalar($quote->getId()) ? (int)$quote->getId() : 0;

        if ($this->shipmentMappingConfigProvider->isUsingCollectAddressTotalsForShippingEstimationEnabled()) {
            // @phpstan-ignore-next-line
            $shippingMethods = $this->shippingManager->estimateByExtendedAddress($quoteId, $shippingAddress);
        } else {
            $shippingMethods = $this->getShippingMethodsForQuote($quote, $shippingAddress);
        }

        return $shippingMethods;
    }

    /**
     * @param Quote $quote
     * @param Address $shippingAddress
     * @return ShippingMethodInterface[]
     */
    private function getShippingMethodsForQuote(Quote $quote, Address $shippingAddress): array
    {
        $output = [];
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->collectShippingRates();
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();

        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->shippingMethodConverter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }

        return $output;
    }

    private function getShippingAddress(Quote $quote): AddressInterface
    {
        $shippingAddress = $quote->getShippingAddress();
        // @phpstan-ignore-next-line
        if ((empty($shippingAddress->getCountryId()) || !$shippingAddress->getPostcode())
            // @phpstan-ignore-next-line
            && $quote->getCustomer()->getId()
        ) {
            try {
                $customerShippingAddress =
                    // @phpstan-ignore-next-line
                    $this->addressRepository->getById($quote->getCustomer()->getDefaultShipping());
                $customerShippingAddress->getCountryId();
                if ($customerShippingAddress->getCountryId()) {
                    $shippingAddress->setCountryId($customerShippingAddress->getCountryId());
                }
            } catch (LocalizedException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        if (empty($shippingAddress->getCountryId())) {
            $shippingAddress->setCountryId(self::DEFAULT_COUNTRY_ID);
        }

        return $shippingAddress;
    }
}
