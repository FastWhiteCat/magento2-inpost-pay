<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant;

use InPost\InPostPay\Api\Data\Merchant\BestsellersInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Api\ExtensibleDataInterface;

class Bestsellers extends DataObject implements BestsellersInterface, ExtensibleDataInterface
{
    /**
     * @return int|null
     */
    public function getPageSize(): ?int
    {
        $pageSize = $this->getData(self::PAGE_SIZE);

        return is_scalar($pageSize) ? (int)$pageSize : null;
    }

    /**
     * @param int|null $pageSize
     * @return void
     */
    public function setPageSize(?int $pageSize): void
    {
        $this->setData(self::PAGE_SIZE, $pageSize);
    }

    /**
     * @return int|null
     */
    public function getTotalItems(): ?int
    {
        $totalItems = $this->getData(self::TOTAL_ITEMS);

        return is_scalar($totalItems) ? (int)$totalItems : null;
    }

    /**
     * @param int|null $totalItems
     * @return void
     */
    public function setTotalItems(?int $totalItems): void
    {
        $this->setData(self::TOTAL_ITEMS, $totalItems);
    }

    /**
     * @return int|null
     */
    public function getPageIndex(): ?int
    {
        $pageIndex = $this->getData(self::PAGE_INDEX);

        return is_scalar($pageIndex) ? (int)$pageIndex : null;
    }

    /**
     * @param int|null $pageIndex
     * @return void
     */
    public function setPageIndex(?int $pageIndex): void
    {
        $this->setData(self::PAGE_INDEX, $pageIndex);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface[]
     */
    public function getProducts(): array
    {
        $products = $this->getData(self::PRODUCTS);

        return is_array($products) ? $products : [];
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface[] $products
     * @return void
     */
    public function setProducts(array $products): void
    {
        $this->setData(self::PRODUCTS, $products);
    }
}
