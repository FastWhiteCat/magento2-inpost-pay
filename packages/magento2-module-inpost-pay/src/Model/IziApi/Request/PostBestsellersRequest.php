<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Request;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Model\Request;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\ApiConnector\TokenGenerator;
use Laminas\Http\Request as HttpRequest;

class PostBestsellersRequest extends Request implements RequestInterface
{
    protected string $uri = '/v1/izi/products';
    protected string $method = HttpRequest::METHOD_POST;
    private ?int $storeId = null;

    /**
     * @param IziApiConfigProvider $iziApiConfigProvider
     * @param TokenGenerator $tokenGenerator
     */
    public function __construct(
        private readonly IziApiConfigProvider $iziApiConfigProvider,
        private readonly TokenGenerator $tokenGenerator
    ) {
    }

    public function getApiUrl(): string
    {
        return $this->iziApiConfigProvider->getIziApiUrl($this->storeId);
    }

    public function getUri(bool $keepParamsIntact = false): string
    {
        return $this->uri;
    }

    public function getBearerToken(): ?string
    {
        $this->tokenGenerator->cleanTokenCache();

        return $this->tokenGenerator->generate(true, $this->storeId)->getAccessToken();
    }

    public function setStoreId(int $storeId): void
    {
        $this->storeId = $storeId;
    }
}
