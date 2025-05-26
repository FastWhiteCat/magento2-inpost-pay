<?php
declare(strict_types=1);

namespace InPost\InPostPay\Registry;

/**
 * Custom registry for storing shipping method values during configuration saves
 */
class ShippingMethodRegistry
{
    /**
     * Registry collection
     *
     * @var array
     */
    private $registry = [];

    /**
     * Check if a value exists in the registry
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function valueExistsForOtherKeys(string $key, $value): bool
    {
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
     * @param mixed $value
     * @return string|null
     */
    public function getKeyForValue(string $currentKey, $value): ?string
    {
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
     * @param mixed $value
     * @return void
     */
    public function register(string $key, $value): void
    {
        $this->registry[$key] = $value;
    }

    /**
     * Retrieve a value from registry by a key
     *
     * @param string $key
     * @return mixed|null
     */
    public function registry(string $key)
    {
        if (isset($this->registry[$key])) {
            return $this->registry[$key];
        }
        return null;
    }
}
