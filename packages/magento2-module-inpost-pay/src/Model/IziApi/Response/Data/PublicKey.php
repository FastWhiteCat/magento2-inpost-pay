<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Response\Data;

use Magento\Framework\DataObject;

class PublicKey extends DataObject
{
    public const PUBLIC_KEY_BASE64 = 'public_key_base64';
    public const VERSION = 'version';

    public function getPublicKeyBase64(): string
    {
        return (string)$this->getData(self::PUBLIC_KEY_BASE64);
    }

    public function setPublicKeyBase64(string $publicKeyBase64): void
    {
        $this->setData(self::PUBLIC_KEY_BASE64, $publicKeyBase64);
    }

    public function getVersion(): string
    {
        return (string)$this->getData(self::VERSION);
    }

    public function setVersion(string $version): void
    {
        $this->setData(self::VERSION, $version);
    }
}
