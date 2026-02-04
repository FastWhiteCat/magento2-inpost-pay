<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use Laminas\Http\Request as HttpRequest;

class Request
{
    protected string $method = HttpRequest::METHOD_GET;
    protected string $uri = '';
    protected ?string $contentType = null;
    protected array $params = [];

    public function getUri(bool $keepParamsIntact = false): string
    {
        $params = $this->getParams();
        $uri = $this->uri;
        foreach ($params as $key => $value) {
            if (str_contains($this->uri, (string)$key)) {
                $uri = str_replace(sprintf('{%s}', (string)$key), (string)$value, $uri);
                if (!$keepParamsIntact) {
                    unset($params[$key]);
                    $this->setParams($params);
                }
            }
        }

        return (string)preg_replace('/{[a-zA-Z0-9_-]*}/', '', $uri);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getHeaders(bool $keepParamsIntact = false): array
    {
        $headers = [];

        if ($this->getContentType()) {
            $headers[RequestInterface::CONTENT_TYPE] = (string)$this->getContentType();
        }

        $bearer = $this->getBearerToken();
        if ($bearer) {
            $headers[RequestInterface::AUTHORIZATION] = (string)sprintf(RequestInterface::BEARER_PATTERN, $bearer);
        }

        return $headers;
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
