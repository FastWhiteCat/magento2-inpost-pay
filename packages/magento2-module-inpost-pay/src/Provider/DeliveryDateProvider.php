<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use DateTime;
use DateTimeZone;
use Exception;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use InPost\InPostPay\Service\Converter\QuoteToBasket\QuoteToBasketSummaryDataConverter;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class DeliveryDateProvider
{
    private const SECONDS_IN_DAY = 86400;

    public function __construct(
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * This method should be modified with after plugin in case of customized delivery date calculations.
     * If not, configuration timestamp increment will be used.
     *
     * @param ShippingMethodInterface $shippingMethod
     * @param Quote $quote
     * @return int
     */
    public function calculateTimestamp(ShippingMethodInterface $shippingMethod, Quote $quote): int
    {
        try {
            $deadlineInDays = $this->shipmentMappingConfigProvider->getDeliveryDateDeadlineInDays();
            $currentDateTime = new DateTime('now', new DateTimeZone('UTC'));
            $currentTimestamp = strtotime($currentDateTime->format(QuoteToBasketSummaryDataConverter::INPOST_DATE_FORMAT));

            return $currentTimestamp + ($deadlineInDays * self::SECONDS_IN_DAY);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());

            return self::SECONDS_IN_DAY * ShipmentMappingConfigProvider::DEFAULT_DELIVERY_DEADLINE;
        }
    }
}
