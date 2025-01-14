<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant;

interface BestsellersInterface
{
    public const PAGE_SIZE = 'page_size';
    public const TOTAL_ITEMS = 'total_items';
    public const PAGE_INDEX = 'page_index';
    public const CONTENT = 'content';

    /**
     * @return int|null
     */
    public function getPageSize(): ?int;

    /**
     * @param int|null $pageSize
     * @return void
     */
    public function setPageSize(?int $pageSize): void;

    /**
     * @return int|null
     */
    public function getTotalItems(): ?int;

    /**
     * @param int|null $totalItems
     * @return void
     */
    public function setTotalItems(?int $totalItems): void;

    /**
     * @return int|null
     */
    public function getPageIndex(): ?int;

    /**
     * @param int|null $pageIndex
     * @return void
     */
    public function setPageIndex(?int $pageIndex): void;

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface[]
     */
    public function getContent(): array;

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface[] $content
     * @return void
     */
    public function setContent(array $content): void;
}
