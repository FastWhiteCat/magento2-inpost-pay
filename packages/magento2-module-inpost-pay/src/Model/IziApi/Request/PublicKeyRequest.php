<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Request;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Model\Request;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\ApiConnector\TokenGenerator;

class PublicKeyRequest extends Request implements RequestInterface
{
    public const VERSION = 'version';
    protected string $uri = '/v1/izi/signing-keys/public/{version}';

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
        return $this->iziApiConfigProvider->getIziApiUrl();
    }

    public function getBearerToken(): ?string
    {
        return $this->tokenGenerator->generate()->getAccessToken();
    }
}
