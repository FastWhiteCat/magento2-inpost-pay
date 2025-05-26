<?php
declare(strict_types=1);

namespace InPost\InPostPay\Registry;

/**
 * Custom registry for storing shipping method values during configuration saves
 */
class ShippingMethodMappingRegistry
{
    /**
     * Registry collection
     *
     * @var array<string, string|null>
     */
    private array $registry = [];

    /**
     * Check if a value exists in the registry
     *
     * @param string $key
     * @param string|null $value
     * @return bool
     */
    public function valueExistsForOtherKeys(string $key, ?string $value): bool
    {
        // Skip validation if value is not a string or null
        if ($value === null) {
            return false;
        }

        foreach ($this->registry as $registryKey => $registryValue) {
            // Skip checking against the same key
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
     * Get the key that contains the specified value (except the current key)
     *
     * @param string $currentKey
     * @param string|null $value
     * @return string|null
     */
    public function getKeyForValue(string $currentKey, ?string $value): ?string
    {
        // Skip validation if value is not a string or null
        if ($value === null) {
            return null;
        }

        foreach ($this->registry as $registryKey => $registryValue) {
            // Skip checking against the same key
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
        // Only register if value is a string or null
        $this->registry[$key] = $value;
    }

    /**
     * Retrieve a value from registry by a key
     *
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
