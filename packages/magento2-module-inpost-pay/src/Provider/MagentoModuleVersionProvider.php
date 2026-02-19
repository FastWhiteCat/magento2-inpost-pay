<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use Composer\InstalledVersions;

class MagentoModuleVersionProvider
{
    public const DEFAULT_VERSION = '0.0.0';
    private const PACKAGE_NAME = 'inpost/magento2-module-inpost-pay';

    protected ?string $version = null;

    /**
     * @return string
     */
    public function getVersion(): string
    {
        if ($this->version === null) {
            try {
                $this->version = InstalledVersions::getPrettyVersion(self::PACKAGE_NAME)
                    ?? self::DEFAULT_VERSION;
            } catch (\Throwable $e) {
                $this->version = self::DEFAULT_VERSION;
            }
        }

        return $this->version;
    }
}
