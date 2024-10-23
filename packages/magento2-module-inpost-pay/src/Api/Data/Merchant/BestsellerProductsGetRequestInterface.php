<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant;

interface BestsellerProductsGetRequestInterface
{
    public const PAGE_INDEX = 'page_index';
    public const PAGE_SIZE = 'page_size';
    public const PRODUCT_ID = 'product_id';

    /**
     * @return string|null
     */
    public function getPageIndex(): ?string;

    /**
     * @param string|null $pageIndex
     * @return void
     */
    public function setPageIndex(?string $pageIndex): void;

    /**
     * @return string|null
     */
    public function getPageSize(): ?string;

    /**
     * @param string|null $pageSize
     * @return void
     */
    public function setPageSize(?string $pageSize): void;

    /**
     * @return string|null
     */
    public function getProductId(): ?string;

    /**
     * @param string|null $productId
     * @return void
     */
    public function setProductId(?string $productId): void;
}
