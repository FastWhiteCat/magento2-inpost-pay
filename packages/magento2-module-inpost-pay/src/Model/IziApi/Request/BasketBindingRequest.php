<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Request;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Model\Request;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\ApiConnector\TokenGenerator;
use Laminas\Http\Request as HttpRequest;

class BasketBindingRequest extends Request implements RequestInterface
{
    protected string $uri = '/v1/izi/basket/{basket_id}/binding';

    protected string $method = HttpRequest::METHOD_POST;

    protected ?string $contentType = 'application/json';

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
        $uri = $this->uri;
        foreach ($this->getParams() as $key => $value) {
            if (is_array($value)) {
                continue;
            }

            $uri = str_replace(sprintf('{%s}', (string)$key), (string)$value, $uri);
        }

        return (string)preg_replace('/{[a-zA-Z0-9_-]*}/', '', $uri);
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
