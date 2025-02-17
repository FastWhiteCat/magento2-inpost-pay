<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Request;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Model\Request;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\ApiConnector\TokenGenerator;
use Laminas\Http\Request as HttpRequest;

class DeleteBestsellerRequest extends Request implements RequestInterface
{
    private const PRODUCT_ID_PARAM = 'product_id';

    protected string $uri = '/v1/izi/product/{product_id}';

    protected string $method = HttpRequest::METHOD_DELETE;

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

        if (array_key_exists(self::PRODUCT_ID_PARAM, $params)
            && is_scalar($params[self::PRODUCT_ID_PARAM])
        ) {
            $productId = (string)$params[self::PRODUCT_ID_PARAM];
            $uri = str_replace(sprintf('{%s}', self::PRODUCT_ID_PARAM), $productId, $uri);

            if (!$keepParamsIntact) {
                unset($params[self::PRODUCT_ID_PARAM]);
                $this->setParams($params);
            }
        }

        return $uri;
    }

    public function getApiUrl(): string
    {
        return $this->iziApiConfigProvider->getIziApiUrl($this->storeId);
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
