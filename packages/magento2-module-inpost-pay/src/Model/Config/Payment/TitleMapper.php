<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Payment;

class TitleMapper
{
    /**
     * @param array $mappings
     */
    public function __construct(
        protected array $mappings = []
    ) {
    }

    public function getTitle(string $code): ?string
    {
        return $this->mappings[$code] ?? null;
    }

    public function getMappings(): array
    {
        return $this->mappings;
    }
}
