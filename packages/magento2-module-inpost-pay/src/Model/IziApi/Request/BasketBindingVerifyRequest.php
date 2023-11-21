<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Request;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Model\Request;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\ApiConnector\TokenGenerator;

class BasketBindingVerifyRequest extends Request implements RequestInterface
{
    protected string $uri = '/v1/izi/basket/{basket_id}/binding';

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
        $params = $this->getParams();
        foreach ($params as $key => $value) {
            if (str_contains($this->uri, $key)) {
                $this->uri = str_replace(sprintf('{%s}', (string)$key), (string)$value, $this->uri);
                unset($params[$key]);
            }
        }

        $this->setParams($params);

        return (string)preg_replace('/{[a-zA-Z0-9_-]*}/', '', $this->uri);
    }

    public function getApiUrl(): string
    {
        return $this->iziApiConfigProvider->getIziApiUrl();
    }

    public function getBearerToken(): ?string
    {
        return $this->tokenGenerator->generate()->getAccessToken();
    }
}
