<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Request;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Model\Request;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\ApiConnector\TokenGenerator;
use Laminas\Http\Request as HttpRequest;

class BasketBindingDeleteRequest extends Request implements RequestInterface
{
    private const BASKET_ID_PARAM = 'basket_id';

    protected string $uri = '/v1/izi/basket/{basket_id}/binding';

    protected string $method = HttpRequest::METHOD_DELETE;

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

    public function getUri(bool $keepParamsIntact = false): string
    {
        $uri = $this->uri;
        $params = $this->getParams();
        if (array_key_exists(self::BASKET_ID_PARAM, $params)
            && is_scalar($params[self::BASKET_ID_PARAM])
        ) {
            $browserId = (string)$params[self::BASKET_ID_PARAM];
            $uri = str_replace(sprintf('{%s}', self::BASKET_ID_PARAM), $browserId, $uri);
            if (!$keepParamsIntact) {
                unset($params[self::BASKET_ID_PARAM]);
                $this->setParams($params);
            }
        }

        return $uri;
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
