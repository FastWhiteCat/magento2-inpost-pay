<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Address;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class StreetLinesLimiter
{
    private const CONFIG_PATH_STREET_LINES = 'customer/address/street_lines';
    private const DEFAULT_LIMIT = 2;
    private const MIN_LIMIT = 1;
    private const MAX_LIMIT = 4;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @param array $lines
     * @return array
     */
    public function limitLines(array $lines): array
    {
        $normalized = array_map(static fn($v) => (string)$v, $lines);
        $normalized = array_map(static fn(string $v) => trim($v), $normalized);
        $normalized = array_values(array_filter($normalized, static fn(string $v) => $v !== ''));
        $limit = $this->getLimit();
        $count = count($normalized);

        if ($count <= $limit) {
            return $normalized;
        }

        if ($limit === 1) {
            $streetName = $normalized[0] ?? '';
            $restOfTheAddressParts = array_slice($normalized, 1);
            $restOfTheAddress = implode('/', $restOfTheAddressParts);
            $singleLineAddress = trim($streetName . ' ' . $restOfTheAddress);

            return [$singleLineAddress];
        }

        $streetLines = array_slice($normalized, 0, $limit);
        $remainingLines = array_slice($normalized, $limit);

        if (!empty($remainingLines)) {
            $streetLines[$limit - 1] = rtrim($streetLines[$limit - 1]) . '/' . implode('/', $remainingLines);
        }

        return $streetLines;
    }

    private function getLimit(): int
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH_STREET_LINES, ScopeInterface::SCOPE_STORE);
        $value = is_scalar($value) ? (int)$value : self::DEFAULT_LIMIT;

        if ($value <= 0) {
            $value = self::DEFAULT_LIMIT;
        }

        if ($value <= self::MIN_LIMIT) {
            $value = self::MIN_LIMIT;
        }

        if ($value > self::MAX_LIMIT) {
            $value = self::MAX_LIMIT;
        }

        return $value;
    }
}
