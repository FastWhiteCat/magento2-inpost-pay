<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Delivery;

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
     * This method should be modified with afterPlugin in case of customized delivery date calculations.
     * If not, configuration timestamp increment will be used.
     *
     * Parameters $shippingMethod exist only to allow easier delivery date customized calculation
     *
     * @param ShippingMethodInterface $shippingMethod
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function calculateDeliveryDate(ShippingMethodInterface $shippingMethod): string
    {
        try {
            $deadlineInDays = $this->shipmentMappingConfigProvider->getDeliveryDateDeadlineInDays();
            $currentDateTime = new DateTime('now', new DateTimeZone('UTC'));
            $currentTimestamp = strtotime(
                $currentDateTime->format(QuoteToBasketSummaryDataConverter::INPOST_DATE_FORMAT)
            );

            return $this->formatInPostDate($currentTimestamp + ($deadlineInDays * self::SECONDS_IN_DAY));
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());

            return $this->formatInPostDate(
                self::SECONDS_IN_DAY * ShipmentMappingConfigProvider::DEFAULT_DELIVERY_DEADLINE
            );
        }
    }

    private function formatInPostDate(int $deliveryTimestamp): string
    {
        $deliveryDateTime = new DateTime();
        $deliveryDateTime->setTimestamp($deliveryTimestamp);

        return $deliveryDateTime->format(QuoteToBasketSummaryDataConverter::INPOST_DATE_FORMAT);
    }
}
