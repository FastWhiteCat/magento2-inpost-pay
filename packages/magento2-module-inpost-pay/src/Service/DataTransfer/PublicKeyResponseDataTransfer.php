<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer;

use InPost\InPostPay\Model\IziApi\Response\Data\PublicKey;
use InPost\InPostPay\Model\IziApi\Response\PublicKeyResponse;
use InPost\InPostPay\Model\IziApi\Response\PublicKeyResponseFactory;
use InPost\InPostPay\Model\IziApi\Response\Data\PublicKeyFactory;

class PublicKeyResponseDataTransfer
{
    public function __construct(
        private readonly PublicKeyResponseFactory $publicKeyResponseFactory,
        private readonly PublicKeyFactory $publicKeyFactory
    ) {
    }

    public function convertToArray(PublicKeyResponse $response): array
    {
        $responseData = $response->toArray();
        $publicKeys = [];
        foreach ($response->getPublicKeys() as $publicKey) {
            $publicKeys[] = $publicKey->toArray();
        }

        $responseData[PublicKeyResponse::PUBLIC_KEYS] = $publicKeys;

        return $responseData;
    }

    public function convertToResponseObject(array $responseData): PublicKeyResponse
    {
        $publicKeys = [];
        $merchantExternalId = (string)($responseData[PublicKeyResponse::MERCHANT_EXTERNAL_ID] ?? '');

        if (array_key_exists(PublicKeyResponse::PUBLIC_KEYS, $responseData)) {
            $publicKeysNode = (array)($responseData[PublicKeyResponse::PUBLIC_KEYS]);

            foreach ($publicKeysNode as $publicKeyNode) {
                $publicKeyBase64 = (string)($publicKeyNode[PublicKey::PUBLIC_KEY_BASE64] ?? '');
                $version = (string)($publicKeyNode[PublicKey::VERSION] ?? '');
                $publicKeys[] = $this->createPublicKey($publicKeyBase64, $version);
            }
        } elseif (array_key_exists(PublicKeyResponse::PUBLIC_KEY, $responseData)) {
            $publicKeyNode = (array)($responseData[PublicKeyResponse::PUBLIC_KEY]);
            $publicKeyBase64 = (string)($publicKeyNode[PublicKey::PUBLIC_KEY_BASE64] ?? '');
            $version = (string)($publicKeyNode[PublicKey::VERSION] ?? '');
            $publicKeys[] = $this->createPublicKey($publicKeyBase64, $version);
        }

        /** @var PublicKeyResponse $publicKeyResponse */
        $publicKeyResponse = $this->publicKeyResponseFactory->create();
        $publicKeyResponse->setMerchantExternalId($merchantExternalId);
        $publicKeyResponse->setPublicKeys($publicKeys);

        return $publicKeyResponse;
    }

    /**
     * @param string $publicKeyBase64
     * @param string $version
     * @return PublicKey
     */
    private function createPublicKey(string $publicKeyBase64, string $version): PublicKey
    {
        /** @var PublicKey $publicKey */
        $publicKey = $this->publicKeyFactory->create();
        $publicKey->setPublicKeyBase64($publicKeyBase64);
        $publicKey->setVersion($version);

        return $publicKey;
    }
}
