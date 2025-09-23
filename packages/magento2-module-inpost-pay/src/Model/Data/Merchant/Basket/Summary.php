<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\Summary\NoticeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\SummaryInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class Summary extends DataObject implements SummaryInterface, ExtensibleDataInterface
{
    private const DEFAULT_CURRENCY = 'PLN';

    /**
     * @param PriceInterfaceFactory $priceFactory
     * @param array $data
     */
    public function __construct(
        private readonly PriceInterfaceFactory $priceFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getBasketBasePrice(): PriceInterface
    {
        $basketBasePrice = $this->getData(self::BASKET_BASE_PRICE);

        if ($basketBasePrice instanceof PriceInterface) {
            return $basketBasePrice;
        }

        return $this->priceFactory->create();
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $basketBasePrice
     * @return void
     */
    public function setBasketBasePrice(PriceInterface $basketBasePrice): void
    {
        $this->setData(self::BASKET_BASE_PRICE, $basketBasePrice);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getBasketFinalPrice(): PriceInterface
    {
        $basketFinalPrice = $this->getData(self::BASKET_FINAL_PRICE);

        if ($basketFinalPrice instanceof PriceInterface) {
            return $basketFinalPrice;
        }

        return $this->priceFactory->create();
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $basketFinalPrice
     * @return void
     */
    public function setBasketFinalPrice(PriceInterface $basketFinalPrice): void
    {
        $this->setData(self::BASKET_FINAL_PRICE, $basketFinalPrice);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface
     */
    public function getBasketPromoPrice(): PriceInterface
    {
        $basketPromoPrice = $this->getData(self::BASKET_PROMO_PRICE);

        if ($basketPromoPrice instanceof PriceInterface) {
            return $basketPromoPrice;
        }

        return $this->priceFactory->create();
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface $basketPromoPrice
     * @return void
     */
    public function setBasketPromoPrice(PriceInterface $basketPromoPrice): void
    {
        $this->setData(self::BASKET_PROMO_PRICE, $basketPromoPrice);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\Summary\NoticeInterface|null
     */
    public function getBasketNotice(): ?NoticeInterface
    {
        $basketNotice = $this->getData(self::BASKET_NOTICE);

        if ($basketNotice instanceof NoticeInterface) {
            return $basketNotice;
        }

        return null;
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\Summary\NoticeInterface|null $basketNotice
     * @return void
     */
    public function setBasketNotice(?NoticeInterface $basketNotice): void
    {
        $this->setData(self::BASKET_NOTICE, $basketNotice);
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        $currency = $this->getData(self::CURRENCY);

        return is_scalar($currency) ? (string)$currency : self::DEFAULT_CURRENCY;
    }

    /**
     * @param string $currency
     * @return void
     */
    public function setCurrency(string $currency): void
    {
        $this->setData(self::CURRENCY, $currency);
    }

    /**
     * @return string
     */
    public function getBasketAdditionalInformation(): string
    {
        $additionalInfo = $this->getData(self::BASKET_ADDITIONAL_INFORMATION);

        return is_scalar($additionalInfo) ? (string)$additionalInfo : '';
    }

    /**
     * @param string $basketAdditionalInformation
     * @return void
     */
    public function setBasketAdditionalInformation(string $basketAdditionalInformation): void
    {
        $this->setData(self::BASKET_ADDITIONAL_INFORMATION, $basketAdditionalInformation);
    }

    /**
     * @return string[]
     */
    public function getPaymentType(): array
    {
        $paymentTypes = $this->getData(self::PAYMENT_TYPE);

        return is_array($paymentTypes) ? $paymentTypes : [];
    }

    /**
     * @param string[] $paymentType
     * @return void
     */
    public function setPaymentType(array $paymentType): void
    {
        $this->setData(self::PAYMENT_TYPE, $paymentType);
    }

    /**
     * @return string|null
     */
    public function getBasketExpirationDate(): ?string
    {
        $basketExpirationDate = $this->getData(self::BASKET_EXPIRATION_DATE);

        return is_scalar($basketExpirationDate) ? (string)$basketExpirationDate : null;
    }

    /**
     * @param string|null $basketExpirationDate
     * @return void
     */
    public function setBasketExpirationDate(?string $basketExpirationDate): void
    {
        $this->setData(self::BASKET_EXPIRATION_DATE, $basketExpirationDate);
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getFreeBasket(): bool
    {
        $freeBasket = $this->getData(self::FREE_BASKET);

        return is_bool($freeBasket) && (bool)$freeBasket;
    }

    /**
     * @param bool $freeBasket
     * @return void
     */
    public function setFreeBasket(bool $freeBasket): void
    {
        $this->setData(self::FREE_BASKET, $freeBasket);
    }
}
