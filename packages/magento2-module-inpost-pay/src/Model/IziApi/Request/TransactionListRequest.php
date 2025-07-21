<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Request;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Model\Request;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\ApiConnector\TokenGenerator;

class TransactionListRequest extends Request implements RequestInterface
{
    protected string $uri = '/v1/izi/transaction';
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

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getUri(bool $keepParamsIntact = false): string
    {
        $uri = $this->uri;
        $params = $this->getParams();
        $urlQuery = http_build_query($params);

        if ($urlQuery) {
            $uri = sprintf('%s?%s', $uri, $urlQuery);
        }

        return $uri;
    }

    public function getApiUrl(): string
    {
        $storeId = $this->storeId ?? 0;

        return $this->iziApiConfigProvider->getIziApiUrl($storeId);
    }

    public function getBearerToken(): ?string
    {
        $storeId = $this->storeId ?? 0;

        return $this->tokenGenerator->generate(false, $storeId)->getAccessToken();
    }

    /**
     * @param int $storeId
     * @return void
     */
    public function setStoreId(int $storeId): void
    {
        $this->storeId = $storeId;
    }
}
