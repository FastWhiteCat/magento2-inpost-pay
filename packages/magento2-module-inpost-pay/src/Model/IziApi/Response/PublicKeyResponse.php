<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Response;

use InPost\InPostPay\Model\IziApi\Response\Data\PublicKey;
use Magento\Framework\DataObject;

class PublicKeyResponse extends DataObject
{
    public const MERCHANT_EXTERNAL_ID = 'merchant_external_id';
    public const PUBLIC_KEYS = 'public_keys';
    public const PUBLIC_KEY = 'public_key';

    public function getMerchantExternalId(): string
    {
        $merchantExternalId = $this->getData(self::MERCHANT_EXTERNAL_ID);

        return is_scalar($merchantExternalId) ? (string)$merchantExternalId : '';
    }

    public function setMerchantExternalId(string $merchantExternalId): void
    {
        $this->setData(self::MERCHANT_EXTERNAL_ID, $merchantExternalId);
    }

    public function getPublicKeys(): array
    {
        $publicKeys = $this->getData(self::PUBLIC_KEYS);

        return is_array($publicKeys) ? $publicKeys : [];
    }

    public function setPublicKeys(array $publicKeys): void
    {
        $this->setData(self::PUBLIC_KEYS, $publicKeys);
    }
}
