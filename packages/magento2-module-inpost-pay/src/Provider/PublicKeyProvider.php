<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use InPost\InPostPay\Model\IziApi\Response\PublicKeyResponse;
use InPost\InPostPay\Model\Cache\PublicKey\Type as PublicKeyCacheType;
use InPost\InPostPay\Service\ApiConnector\PublicKeyGenerator;
use InPost\InPostPay\Service\Converter\PublicKeyResponseDataConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

class PublicKeyProvider
{
    private array $cachedResponses = [];

    public function __construct(
        private readonly PublicKeyCacheType $publicKeyCacheType,
        private readonly PublicKeyGenerator $publicKeyGenerator,
        private readonly PublicKeyResponseDataConverter $publicKeyResponseDataConverter,
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * @param string $version
     * @return string
     * @throws LocalizedException
     */
    public function getPublicKeyBase64(string $version): string
    {
        $publicKeys = $this->getPublicKeyResponse($version)->getPublicKeys();
        foreach ($publicKeys as $publicKey) {
            if ($version === $publicKey->getVersion()) {
                return $publicKey->getPublicKeyBase64();
            }
        }

        return '';
    }

    /**
     * @param string $version
     * @return string
     * @throws LocalizedException
     */
    public function getMerchantExternalId(string $version): string
    {
        return $this->getPublicKeyResponse($version)->getMerchantExternalId();
    }

    /**
     * @param string $version
     * @return PublicKeyResponse
     * @throws LocalizedException
     */
    private function getPublicKeyResponse(string $version): PublicKeyResponse
    {
        if (isset($this->cachedResponses[$version]) && $this->cachedResponses[$version] instanceof PublicKeyResponse) {
            return $this->cachedResponses[$version];
        }

        $encodedPublicKeyData = (string)$this->publicKeyCacheType->load($version);
        if (empty($encodedPublicKeyData)) {
            $publicKeyResponse = $this->publicKeyGenerator->generate($version);
            $encodedPublicKeyData = (string)$this->serializer->serialize(
                $this->publicKeyResponseDataConverter->convertToArray($publicKeyResponse)
            );
            $this->publicKeyCacheType->save(
                $encodedPublicKeyData,
                $version,
                [PublicKeyCacheType::CACHE_TAG],
                PublicKeyCacheType::TTL
            );
        }

        $this->cachedResponses[$version] = $this->publicKeyResponseDataConverter->convertToResponseObject(
            (array)$this->serializer->unserialize($encodedPublicKeyData)
        );

        return $this->cachedResponses[$version];
    }
}
