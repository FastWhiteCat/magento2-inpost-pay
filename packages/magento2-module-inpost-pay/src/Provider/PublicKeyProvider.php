<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use InPost\InPostPay\Model\IziApi\Response\PublicKeyResponse;
use InPost\InPostPay\Model\Cache\PublicKey\Type as PublicKeyCacheType;
use Magento\Framework\App\CacheInterface;
use InPost\InPostPay\Service\ApiConnector\PublicKeyGenerator;
use InPost\InPostPay\Service\DataTransfer\PublicKeyResponseDataTransfer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;

class PublicKeyProvider
{
    private array $cachedResponses = [];

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly PublicKeyGenerator $publicKeyGenerator,
        private readonly PublicKeyResponseDataTransfer $publicKeyResponseDataTransfer,
        private readonly SerializerInterface $serializer,
        private readonly StoreManagerInterface $storeManager
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
        $currentStoreId = $this->getCurrentStoreId();
        $cacheIdentifier = sprintf('%s_%s_%s', PublicKeyCacheType::TYPE_IDENTIFIER, $version, $currentStoreId);

        if (isset($this->cachedResponses[$cacheIdentifier])
            && $this->cachedResponses[$cacheIdentifier] instanceof PublicKeyResponse
        ) {
            return $this->cachedResponses[$cacheIdentifier];
        }

        $encodedPublicKeyData = (string)$this->cache->load($cacheIdentifier);
        if (empty($encodedPublicKeyData)) {
            $publicKeyResponse = $this->publicKeyGenerator->generate($version);
            $encodedPublicKeyData = (string)$this->serializer->serialize(
                $this->publicKeyResponseDataTransfer->convertToArray($publicKeyResponse)
            );
            $this->cache->save(
                $encodedPublicKeyData,
                $cacheIdentifier,
                [PublicKeyCacheType::CACHE_TAG],
                PublicKeyCacheType::TTL
            );
        }

        $this->cachedResponses[$cacheIdentifier] = $this->publicKeyResponseDataTransfer->convertToResponseObject(
            (array)$this->serializer->unserialize($encodedPublicKeyData)
        );

        return $this->cachedResponses[$cacheIdentifier];
    }

    private function getCurrentStoreId(): int
    {
        try {
            $storeId = (int)$this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $e) {
            $storeId = 0;
        }

        return $storeId;
    }
}
