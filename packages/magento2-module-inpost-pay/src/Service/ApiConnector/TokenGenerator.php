<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Exception\InPostPayInvalidConfigurationException;
use InPost\InPostPay\Model\AuthApi\Request\OAuthTokenRequest as TokenRequest;
use InPost\InPostPay\Model\AuthApi\Response\OAuthTokenResponse as TokenResponse;
use InPost\InPostPay\Model\AuthApi\Request\OAuthTokenRequestFactory as TokenRequestFactory;
use InPost\InPostPay\Model\AuthApi\Response\OAuthTokenResponseFactory as TokenResponseFactory;
use InPost\InPostPay\Provider\Config\AuthConfigProvider;
use Psr\Log\LoggerInterface;

class TokenGenerator
{
    private ?TokenResponse $tokenResponse = null;

    /**
     * @param ConnectorInterface $connector
     * @param AuthConfigProvider $authConfigProvider
     * @param TokenRequestFactory $tokenRequestFactory
     * @param TokenResponseFactory $tokenResponseFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly AuthConfigProvider $authConfigProvider,
        private readonly TokenRequestFactory $tokenRequestFactory,
        private readonly TokenResponseFactory $tokenResponseFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return TokenResponse
     * @throws InPostPayInvalidConfigurationException
     * @throws Exception
     */
    public function generate(): TokenResponse
    {
        if ($this->tokenResponse === null) {
            try {
                /** @var TokenRequest $request */
                $request = $this->tokenRequestFactory->create();
                $request->setParams(
                    [
                        TokenRequest::CLIENT_ID => $this->authConfigProvider->getClientId(),
                        TokenRequest::CLIENT_SECRET => $this->authConfigProvider->getClientSecret(),
                        TokenRequest::GRANT_TYPE => TokenRequest::CREDENTIAL_GRANT_TYPE,
                    ]
                );
                $result = $this->connector->sendRequest($request);
                $this->tokenResponse = $this->handle($result);
            } catch (InPostPayInvalidConfigurationException $e) {
                $this->logger->error(
                    __('Could not generate token due to invalid configuration. Details: %1', $e->getMessage())
                );

                throw $e;
            } catch (Exception $e) {
                $this->logger->critical(
                    __('There was a problem with processing token generation request. Details: %1', $e->getMessage())
                );

                throw $e;
            }
        }

        return $this->tokenResponse;
    }

    private function handle(array $result): TokenResponse
    {
        $accessToken = (string)($result[TokenResponse::ACCESS_TOKEN] ?? '');
        $expiresIn = (int)($result[TokenResponse::EXPIRES_IN] ?? 0);
        $refreshExpiresIn = (int)($result[TokenResponse::REFRESH_EXPIRES_IN] ?? 0);
        $tokenType = (string)($publicKeysNode[TokenResponse::TOKEN_TYPE] ?? '');
        $notBeforePolicy = (int)($publicKeysNode[TokenResponse::NOT_BEFORE_POLICY] ?? 0);
        $scope = (string)($publicKeysNode[TokenResponse::SCOPE] ?? '');

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
}
