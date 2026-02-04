<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Response;

use Magento\Framework\DataObject;

class TransactionListResponse extends DataObject
{
    public const ITEMS = 'items';
    public const PAGE = 'page';
    public const PER_PAGE = 'per_page';
    public const COUNT = 'count';

    public function getPage(): int
    {
        $page = $this->getData(self::PAGE);

        return is_scalar($page) ? (int)$page : 0;
    }

    public function setPage(int $page): void
    {
        $this->setData(self::PAGE, $page);
    }

    public function getPerPage(): int
    {
        $perPage = $this->getData(self::PER_PAGE);

        return is_scalar($perPage) ? (int)$perPage : 0;
    }

    public function setPerPage(int $perPage): void
    {
        $this->setData(self::PER_PAGE, $perPage);
    }

    public function getCount(): int
    {
        $count = $this->getData(self::COUNT);

        return is_scalar($count) ? (int)$count : 0;
    }

    public function setCount(int $count): void
    {
        $this->setData(self::COUNT, $count);
    }

    public function getItems(): array
    {
        $items = $this->getData(self::ITEMS);

        return is_array($items) ? $items : [];
    }

    public function setItems(array $items): void
    {
        $this->setData(self::ITEMS, $items);
    }
}
