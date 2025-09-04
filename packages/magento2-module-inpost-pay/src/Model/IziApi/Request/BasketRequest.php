<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Request;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Model\Request;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\ApiConnector\TokenGenerator;
use Laminas\Http\Client as HttpClient;
use Laminas\Http\Request as HttpRequest;
use Magento\Analytics\Model\Connector\Http\JsonConverter;

class BasketRequest extends Request implements RequestInterface
{
    private const BASKET_ID_PARAM = 'basket_id';

    protected string $method = HttpRequest::METHOD_PUT;
    protected ?string $contentType = JsonConverter::CONTENT_MEDIA_TYPE;

    protected string $uri = '/v2/izi/basket/{basket_id}';

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
        if (array_key_exists(self::BASKET_ID_PARAM, $params)
            && is_scalar($params[self::BASKET_ID_PARAM])
        ) {
            $basketId = (string)$params[self::BASKET_ID_PARAM];
            $uri = str_replace(sprintf('{%s}', self::BASKET_ID_PARAM), $basketId, $uri);
            if (!$keepParamsIntact) {
                unset($params[self::BASKET_ID_PARAM]);
                $this->setParams($params);
            }
        }

        return $uri;
    }

    public function getApiUrl(): string
    {
        $storeId = null;
        $isAsync = $this->iziApiConfigProvider->isAsyncBasketExportEnabled();

        if ($isAsync) {
            $storeId = $this->storeId ?? 0;
        }

        return $this->iziApiConfigProvider->getIziApiUrl($storeId);
    }

    public function getBearerToken(): ?string
    {
        $storeId = null;
        $isAsync = $this->iziApiConfigProvider->isAsyncBasketExportEnabled();

        if ($isAsync) {
            $storeId = $this->storeId ?? 0;
        }

        return $this->tokenGenerator->generate($isAsync, $storeId)->getAccessToken();
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
