<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Model\AuthApi\Request\OAuthTokenRequest as TokenRequest;
use InPost\InPostPay\Model\AuthApi\Response\OAuthTokenResponse as TokenResponse;
use InPost\InPostPay\Model\AuthApi\Request\OAuthTokenRequestFactory as TokenRequestFactory;
use InPost\InPostPay\Model\AuthApi\Response\OAuthTokenResponseFactory as TokenResponseFactory;
use InPost\InPostPay\Provider\Config\AuthConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\CacheInterface;
use InPost\InPostPay\Model\Cache\OAuthToken\Type as OAuthTokenCache;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TokenGenerator
{
    public const EXPIRES_IN_SAFETY_BUFFER = 60;

    private array $tokenResponses = [];

    /**
     * @param ConnectorInterface $connector
     * @param AuthConfigProvider $authConfigProvider
     * @param TokenRequestFactory $tokenRequestFactory
     * @param TokenResponseFactory $tokenResponseFactory
     * @param StoreManagerInterface $storeManager
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly AuthConfigProvider $authConfigProvider,
        private readonly TokenRequestFactory $tokenRequestFactory,
        private readonly TokenResponseFactory $tokenResponseFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly CacheInterface $cache,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param bool|null $forceNew
     * @param int|null $storeId
     * @return TokenResponse
     * @throws LocalizedException
     */
    public function generate(?bool $forceNew = false, ?int $storeId = null): TokenResponse
    {
        $storeId = $storeId ?? $this->getCurrentStoreId();
        $cacheIdentifier = $this->getTokenCacheIdentifier($storeId);
        $tokenResponse = $this->getCachedTokenResponse($cacheIdentifier);

        if (!$forceNew && $tokenResponse) {
            return $tokenResponse;
        }

        try {
            /** @var TokenRequest $request */
            $request = $this->tokenRequestFactory->create();
            $request->setStoreId($storeId);
            $request->setParams(
                [
                    TokenRequest::CLIENT_ID => $this->authConfigProvider->getClientId($storeId),
                    TokenRequest::CLIENT_SECRET => $this->authConfigProvider->getClientSecret($storeId),
                    TokenRequest::GRANT_TYPE => TokenRequest::CREDENTIAL_GRANT_TYPE,
                ]
            );
            $result = $this->connector->sendRequest($request);
            $this->setCachedTokenResponseData($cacheIdentifier, $result);
            $this->tokenResponses[$cacheIdentifier] = $this->handle($result);
        } catch (InPostPayInternalException $e) {
            $errorPhrase = __(
                'Could not generate token due to invalid configuration. Details: %1',
                $e->getMessage()
            );
            $this->logger->error($errorPhrase->render());

            throw new LocalizedException($errorPhrase);
        } catch (Exception $e) {
            $errorPhrase = __(
                'There was a problem with processing token generation request. Details: %1',
                $e->getMessage()
            );
            $this->logger->critical($errorPhrase->render());

            throw new LocalizedException($errorPhrase);
        }

        return $this->tokenResponses[$cacheIdentifier];
    }

    private function getCachedTokenResponse(string $cacheIdentifier): ?TokenResponse
    {
        if (isset($this->tokenResponses[$cacheIdentifier])) {
            return $this->tokenResponses[$cacheIdentifier];
        }

        $encodedTokenResponse = $this->cache->load($cacheIdentifier);

        if (!empty($encodedTokenResponse)) {
            $tokenResponseData = $this->serializer->unserialize($encodedTokenResponse);

            if (!is_array($tokenResponseData)) {
                $tokenResponseData = [];
            }

            $this->tokenResponses[$cacheIdentifier] = $this->handle($tokenResponseData);
        } else {
            $this->tokenResponses[$cacheIdentifier] = null;
        }

        return $this->tokenResponses[$cacheIdentifier];
    }

    private function setCachedTokenResponseData(string $cacheIdentifier, array $tokenResponseData): void
    {
        $expiresIn = (int)($tokenResponseData[TokenResponse::EXPIRES_IN] ?? 0);
        $expiresIn -= self::EXPIRES_IN_SAFETY_BUFFER;

        if ($expiresIn > 0) {
            $encodedTokenResponseData = $this->serializer->serialize($tokenResponseData);
            $this->cache->save(
                is_string($encodedTokenResponseData) ? $encodedTokenResponseData : '',
                $cacheIdentifier,
                [OAuthTokenCache::CACHE_TAG],
                $expiresIn
            );
        }
    }

    private function getTokenCacheIdentifier(?int $storeId = null): string
    {
        if ($storeId === null) {
            $storeId = $this->getCurrentStoreId();
        }

        return sprintf(
            '%s_%s',
            OAuthTokenCache::TYPE_IDENTIFIER,
            $storeId
        );
    }

    public function cleanTokenCache(): void
    {
        $this->tokenResponses = [];
    }

    private function handle(array $result): TokenResponse
    {
        $accessToken = (string)($result[TokenResponse::ACCESS_TOKEN] ?? '');
        $expiresIn = (int)($result[TokenResponse::EXPIRES_IN] ?? 0);
        $refreshExpiresIn = (int)($result[TokenResponse::REFRESH_EXPIRES_IN] ?? 0);
        $tokenType = (string)($result[TokenResponse::TOKEN_TYPE] ?? '');
        $notBeforePolicy = (int)($result[TokenResponse::NOT_BEFORE_POLICY] ?? 0);
        $scope = (string)($result[TokenResponse::SCOPE] ?? '');

        /** @var TokenResponse $tokenResponse */
        $tokenResponse = $this->tokenResponseFactory->create();
        $tokenResponse->setAccessToken($accessToken);
        $tokenResponse->setExpiresIn($expiresIn);
        $tokenResponse->setRefreshExpiresIn($refreshExpiresIn);
        $tokenResponse->setTokenType($tokenType);
        $tokenResponse->setNotBeforePolicy($notBeforePolicy);
        $tokenResponse->setScope($scope);

        return $tokenResponse;
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
