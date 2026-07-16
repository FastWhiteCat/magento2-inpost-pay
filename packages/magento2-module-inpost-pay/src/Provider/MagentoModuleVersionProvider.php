<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class MagentoModuleVersionProvider
{
    public const DEFAULT_VERSION = '0.0.0';
    private const PACKAGE_NAME = 'inpost/magento2-module-inpost-pay';

    private ?string $version = null;

    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly ReadFactory $readFactory,
        private readonly JsonSerializer $jsonSerializer
    ) {
    }

    public function getVersion(): string
    {
        if ($this->version !== null) {
            return $this->version;
        }

        try {
            $directoryRead = $this->readFactory->create($this->directoryList->getRoot());
            if (!$directoryRead->isFile('composer.lock')) {
                return $this->version = self::DEFAULT_VERSION;
            }

            $lockData = $this->jsonSerializer->unserialize($directoryRead->readFile('composer.lock'));
            $this->version = $this->findPackageVersion($lockData['packages'] ?? []);
        } catch (FileSystemException | ValidatorException) {
            $this->version = self::DEFAULT_VERSION;
        }

        return $this->version;
    }

    private function findPackageVersion(array $packages): string
    {
        foreach ($packages as $package) {
            if (($package['name'] ?? '') === self::PACKAGE_NAME) {
                return ltrim($package['version'] ?? self::DEFAULT_VERSION, 'v');
            }
        }

        return self::DEFAULT_VERSION;
    }
}
