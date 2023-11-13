<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Request;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\ApiConnector\TokenGenerator;
use Laminas\Http\Request;

class PublicKeyRequest implements RequestInterface
{
    public const VERSION = 'version';
    public const URI_PATTERN = '/v1/izi/signing-keys/public/{version}';

    private string $method = Request::METHOD_GET;
    private ?string $contentType = null;
    private array $params = [];

    /**
     * @param IziApiConfigProvider $iziApiConfigProvider
     * @param TokenGenerator $tokenGenerator
     */
    public function __construct(
        private readonly IziApiConfigProvider $iziApiConfigProvider,
        private readonly TokenGenerator $tokenGenerator
    ) {
    }

    public function getUri(): string
    {
        $uri = self::URI_PATTERN;
        foreach ($this->getParams() as $key => $value) {
            $uri = str_replace(sprintf('{%s}', (string)$key), (string)$value, $uri);
        }

        return preg_replace('/{[a-zA-Z0-9_-]*}/', '', $uri);
    }

    public function getApiUrl(): string
    {
        return $this->iziApiConfigProvider->getIziApiUrl();
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
        return $this->tokenGenerator->generate()->getAccessToken();
    }
}
