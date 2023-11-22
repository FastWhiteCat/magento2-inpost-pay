<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use Laminas\Http\Request as HttpRequest;

class Request
{
    protected string $method = HttpRequest::METHOD_GET;
    protected string $uri = '';
    protected ?string $contentType = null;
    protected array $params = [];

    public function getUri(): string
    {
        $uri = $this->uri;
        foreach ($this->getParams() as $key => $value) {
            $uri = str_replace(sprintf('{%s}', (string)$key), (string)$value, $uri);
        }

        return (string)preg_replace('/{[a-zA-Z0-9_-]*}/', '', $uri);
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
