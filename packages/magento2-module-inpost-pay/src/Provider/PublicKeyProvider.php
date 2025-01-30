<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use InPost\InPostPay\Model\IziApi\Response\PublicKeyResponse;
use InPost\InPostPay\Model\Cache\PublicKey\Type as PublicKeyCacheType;
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
        private readonly PublicKeyCacheType $publicKeyCacheType,
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
        $versionWithStoreId = sprintf('%s_%s', $version, $currentStoreId);

        if (isset($this->cachedResponses[$versionWithStoreId])
            && $this->cachedResponses[$versionWithStoreId] instanceof PublicKeyResponse
        ) {
            return $this->cachedResponses[$versionWithStoreId];
        }

        $encodedPublicKeyData = (string)$this->publicKeyCacheType->load($version);
        if (empty($encodedPublicKeyData)) {
            $publicKeyResponse = $this->publicKeyGenerator->generate($version);
            $encodedPublicKeyData = (string)$this->serializer->serialize(
                $this->publicKeyResponseDataTransfer->convertToArray($publicKeyResponse)
            );
            $this->publicKeyCacheType->save(
                $encodedPublicKeyData,
                $versionWithStoreId,
                [PublicKeyCacheType::CACHE_TAG],
                PublicKeyCacheType::TTL
            );
        }

        $this->cachedResponses[$versionWithStoreId] = $this->publicKeyResponseDataTransfer->convertToResponseObject(
            (array)$this->serializer->unserialize($encodedPublicKeyData)
        );

        return $this->cachedResponses[$versionWithStoreId];
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
