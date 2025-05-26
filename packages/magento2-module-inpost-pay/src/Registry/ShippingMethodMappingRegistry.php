<?php
declare(strict_types=1);

namespace InPost\InPostPay\Registry;

class ShippingMethodMappingRegistry
{
    private array $registry = [];

    /**
     * @param string $key
     * @param string|null $value
     * @return bool
     */
    public function valueExistsForOtherKeys(string $key, ?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        foreach ($this->registry as $registryKey => $registryValue) {
            if ($registryKey === $key) {
                continue;
            }

            if ($registryValue === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $currentKey
     * @param string|null $value
     * @return string|null
     */
    public function getKeyForValue(string $currentKey, ?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        foreach ($this->registry as $registryKey => $registryValue) {
            if ($registryKey === $currentKey) {
                continue;
            }

            if ($registryValue === $value) {
                return $registryKey;
            }
        }

        return null;
    }

    /**
     * Register a new value
     *
     * @param string $key
     * @param string|null $value
     * @return void
     */
    public function register(string $key, ?string $value): void
    {
        $this->registry[$key] = $value;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function registry(string $key): ?string
    {
        if (isset($this->registry[$key])) {
            return $this->registry[$key];
        }
        return null;
    }
}
