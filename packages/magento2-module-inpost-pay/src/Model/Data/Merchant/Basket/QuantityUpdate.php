<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\Product\QuantityChangeInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\Product\QuantityChangeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\QuantityUpdateInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class QuantityUpdate extends DataObject implements QuantityUpdateInterface, ExtensibleDataInterface
{
    /**
     * @param QuantityChangeInterfaceFactory $quantityChangeFactory
     * @param array $data
     */
    public function __construct(
        private readonly QuantityChangeInterfaceFactory $quantityChangeFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        $productId = $this->getData(self::PRODUCT_ID);

        return (is_scalar($productId)) ? (string)$productId : '';
    }

    /**
     * @param string $productId
     * @return void
     */
    public function setProductId(string $productId): void
    {
        $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @return string|null
     */
    public function getEan(): ?string
    {
        $ean = $this->getData(self::EAN);

        return (is_scalar($ean)) ? (string)$ean : null;
    }

    /**
     * @param string|null $ean
     * @return void
     */
    public function setEan(?string $ean): void
    {
        $this->setData(self::EAN, $ean);
    }

    /**
     * @return QuantityChangeInterface
     */
    public function getQuantity(): QuantityChangeInterface
    {
        $quantity = $this->getData(self::QUANTITY);

        if ($quantity instanceof QuantityChangeInterface) {
            return $quantity;
        }

        return $this->quantityChangeFactory->create();
    }

    /**
     * @param QuantityChangeInterface $quantity
     * @return void
     */
    public function setQuantity(QuantityChangeInterface $quantity): void
    {
        $this->setData(self::QUANTITY, $quantity);
    }
}
