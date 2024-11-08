<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailableInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailable\DetailsInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailable\DetailsInterfaceFactory;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class PromotionAvailable extends DataObject implements PromotionAvailableInterface, ExtensibleDataInterface
{
    public function __construct(
        private readonly DetailsInterfaceFactory $detailsInterfaceFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        $type = $this->getData(self::TYPE);

        return is_scalar($type) ? (string)$type : '';
    }

    /**
     * @param string $type
     * @return void
     */
    public function setType(string $type): void
    {
        $this->setData(self::TYPE, $type);
    }

    /**
     * @return string
     */
    public function getPromoCodeValue(): string
    {
        $promoCodeValue = $this->getData(self::PROMO_CODE_VALUE);

        return is_scalar($promoCodeValue) ? (string)$promoCodeValue : '';
    }

    /**
     * @param string $promoCodeValue
     * @return void
     */
    public function setPromoCodeValue(string $promoCodeValue): void
    {
        $this->setData(self::PROMO_CODE_VALUE, $promoCodeValue);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        $description = $this->getData(self::DESCRIPTION);

        return is_scalar($description) ? (string)$description : '';
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        $startDate = $this->getData(self::START_DATE);

        return is_scalar($startDate) ? (string)$startDate : '';
    }

    /**
     * @param string $startDate
     * @return void
     */
    public function setStartDate(string $startDate): void
    {
        $this->setData(self::START_DATE, $startDate);
    }

    /**
     * @return string
     */
    public function getEndDate(): string
    {
        $endDate = $this->getData(self::END_DATE);

        return is_scalar($endDate) ? (string)$endDate : '';
    }

    /**
     * @param string $endDate
     * @return void
     */
    public function setEndDate(string $endDate): void
    {
        $this->setData(self::END_DATE, $endDate);
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        $priority = $this->getData(self::PRIORITY);

        return is_scalar($priority) ? (int)$priority : 0;
    }

    /**
     * @param int $priority
     * @return void
     */
    public function setPriority(int $priority): void
    {
        $this->setData(self::PRIORITY, $priority);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailable\DetailsInterface
     */
    public function getDetails(): DetailsInterface
    {
        $details = $this->getData(self::DETAILS);

        if ($details instanceof DetailsInterface) {
            return $details;
        }

        return $this->detailsInterfaceFactory->create();
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PromotionAvailable\DetailsInterface $details
     * @return void
     */
    public function setDetails(DetailsInterface $details): void
    {
        $this->setData(self::DETAILS, $details);
    }
}
