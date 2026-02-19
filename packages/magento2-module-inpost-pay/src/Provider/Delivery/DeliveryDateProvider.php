<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Delivery;

use DateTime;
use DateTimeZone;
use Exception;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use Magento\Quote\Api\Data\ShippingMethodInterface;
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
                $currentDateTime->format(BasketInterface::INPOST_DATE_FORMAT)
            );

            return $this->formatInPostDate($currentTimestamp + ($deadlineInDays * self::SECONDS_IN_DAY));
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());

            return $this->formatInPostDate(
                self::SECONDS_IN_DAY * ShipmentMappingConfigProvider::DEFAULT_DELIVERY_DEADLINE
            );
        }
    }

    /**
     * This method should be modified with afterPlugin in case of customized digital delivery date calculations.
     * If not, configuration timestamp increment will be used.
     *
     * @param int|null $storeId
     * @return string
     */
    public function calculateDigitalDeliveryDate(?int $storeId = null): string
    {
        try {
            $deadlineInSeconds = $this->shipmentMappingConfigProvider->getDigitalDeliveryDateDeadlineInSeconds(
                $storeId
            );
            $currentDateTime = new DateTime('now', new DateTimeZone('UTC'));
            $currentTimestamp = strtotime(
                $currentDateTime->format(BasketInterface::INPOST_DATE_FORMAT)
            );

            return $this->formatInPostDate($currentTimestamp + ($deadlineInSeconds));
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());

            return $this->formatInPostDate(
                self::SECONDS_IN_DAY * ShipmentMappingConfigProvider::DEFAULT_DIGITAL_DELIVERY_DEADLINE
            );
        }
    }

    private function formatInPostDate(int $deliveryTimestamp): string
    {
        $deliveryDateTime = new DateTime();
        $deliveryDateTime->setTimestamp($deliveryTimestamp);

        return $deliveryDateTime->format(BasketInterface::INPOST_DATE_FORMAT);
    }
}
