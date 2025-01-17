<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class MagentoModuleVersionProvider
{
    public const DEFAULT_VERSION = '0.0.0';

    protected ?string $version = null;

    public function __construct(
        private readonly ComponentRegistrarInterface $componentRegistrar,
        private readonly ReadFactory $readFactory,
        private readonly JsonSerializer $jsonSerializer
    ) {
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        if ($this->version === null) {
            try {
                $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'InPost_InPostPay');
                // @phpstan-ignore-next-line
                $directoryRead = $this->readFactory->create($modulePath);
                if ($directoryRead->isFile('composer.json')) {
                    $composerJsonContent = $directoryRead->readFile('composer.json');
                    $composerData = $this->jsonSerializer->unserialize($composerJsonContent);
                    $this->version = $composerData['version'] ?? self::DEFAULT_VERSION;
                }
            } catch (FileSystemException | ValidatorException $e) {
                $this->version = self::DEFAULT_VERSION;
            }
        }

        return $this->version;
    }
}
