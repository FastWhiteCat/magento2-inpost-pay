<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Request;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Model\Request;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\ApiConnector\TokenGenerator;
use Laminas\Http\Request as HttpRequest;

class UpdateOrderRequest extends Request implements RequestInterface
{
    private const ORDER_ID = 'order_id';

    protected string $uri = '/v1/izi/order/{order_id}/event';

    protected string $method = HttpRequest::METHOD_POST;

    protected ?string $contentType = 'application/json';

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

    public function getUri(bool $keepParamsIntact = false): string
    {
        $uri = $this->uri;
        $params = $this->getParams();
        if (array_key_exists(self::ORDER_ID, $params)
            && is_scalar($params[self::ORDER_ID])
        ) {
            $orderId = (string)$params[self::ORDER_ID];
            $uri = str_replace(sprintf('{%s}', self::ORDER_ID), $orderId, $uri);
            if (!$keepParamsIntact) {
                unset($params[self::ORDER_ID]);
                $this->setParams($params);
            }
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
