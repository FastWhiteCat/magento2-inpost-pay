<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\AuthApi\Request;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Model\Request;
use InPost\InPostPay\Provider\Config\AuthConfigProvider;
use Laminas\Http\Client;
use Laminas\Http\Request as HttpRequest;

class OAuthTokenRequest extends Request implements RequestInterface
{
    public const CLIENT_ID = 'client_id';
    public const CLIENT_SECRET = 'client_secret';
    public const GRANT_TYPE = 'grant_type';
    public const CREDENTIAL_GRANT_TYPE = 'client_credentials';

    protected string $method = HttpRequest::METHOD_POST;
    protected string $uri = '/auth/realms/external/protocol/openid-connect/token';
    protected ?string $contentType = Client::ENC_URLENCODED;

    public function __construct(
        private readonly AuthConfigProvider $authConfigProvider
    ) {
    }

    public function getApiUrl(): string
    {
        return $this->authConfigProvider->getAuthTokenUrl();
    }
}
