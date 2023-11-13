<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\AuthApi\Request;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Provider\Config\AuthConfigProvider;
use Laminas\Http\Client;
use Laminas\Http\Request;

class OAuthTokenRequest implements RequestInterface
{
    public const CLIENT_ID = 'client_id';
    public const CLIENT_SECRET = 'client_secret';
    public const GRANT_TYPE = 'grant_type';
    public const CREDENTIAL_GRANT_TYPE = 'client_credentials';
    public const URI = '/auth/realms/external/protocol/openid-connect/token';

    private string $method = Request::METHOD_POST;
    private ?string $contentType = Client::ENC_URLENCODED;
    private array $params = [];

    public function __construct(
        private readonly AuthConfigProvider $authConfigProvider
    ) {
    }

    public function getUri(): string
    {
        return self::URI;
    }

    public function getApiUrl(): string
    {
        return $this->authConfigProvider->getAuthTokenUrl();
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function getBearerToken(): ?string
    {
        return null;
    }
}
