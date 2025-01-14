<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\BestsellerProduct;

use InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\ProductAvailabilityInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Api\ExtensibleDataInterface;

class ProductAvailability extends DataObject implements ProductAvailabilityInterface, ExtensibleDataInterface
{
    /**
     * @return string|null
     */
    public function getStartDate(): ?string
    {
        $startDate = $this->getData(self::START_DATE);

        return is_scalar($startDate) ? (string)$startDate : null;
    }

    /**
     * @param string|null $startDate
     * @return void
     */
    public function setStartDate(?string $startDate): void
    {
        $this->setData(self::START_DATE, $startDate);
    }

    /**
     * @return string|null
     */
    public function getEndDate(): ?string
    {
        $endDate = $this->getData(self::END_DATE);

        return is_scalar($endDate) ? (string)$endDate : null;
    }

    /**
     * @param string|null $endDate
     * @return void
     */
    public function setEndDate(?string $endDate): void
    {
        $this->setData(self::END_DATE, $endDate);
    }
}
