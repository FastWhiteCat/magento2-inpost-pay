<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Request;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Model\Request;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\ApiConnector\TokenGenerator;

class GetBestsellersRequest extends Request implements RequestInterface
{
    private const PAGE_INDEX_PARAM = 'page_index';
    private const PAGE_SIZE_PARAM = 'page_size';

    protected string $uri = '/v1/izi/products';

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
        $pageIndex = null;
        $pageSize = null;

        if (array_key_exists(self::PAGE_INDEX_PARAM, $params)
            && is_scalar($params[self::PAGE_INDEX_PARAM])
        ) {
            $pageIndex = (string)$params[self::PAGE_INDEX_PARAM];

            if (!$keepParamsIntact) {
                unset($params[self::PAGE_INDEX_PARAM]);
                $this->setParams($params);
            }
        }

        if (array_key_exists(self::PAGE_SIZE_PARAM, $params)
            && is_scalar($params[self::PAGE_SIZE_PARAM])
        ) {
            $pageSize = (string)$params[self::PAGE_SIZE_PARAM];

            if (!$keepParamsIntact) {
                unset($params[self::PAGE_SIZE_PARAM]);
                $this->setParams($params);
            }
        }

        return $this->modifyUriWithPageIndexAndSize($uri, $pageIndex, $pageSize);
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

    private function modifyUriWithPageIndexAndSize(
        string $uri,
        ?string $pageIndex = null,
        ?string $pageSize = null
    ): string {
        if ($pageIndex) {
            $uri = sprintf('%s?%s=%s', $this->uri, self::PAGE_INDEX_PARAM, $pageIndex);
        }

        if ($pageSize) {
            $uri = sprintf(
                '%s%s%s=%s',
                $uri,
                !empty($pageIndex) ? '&' : '?',
                self::PAGE_SIZE_PARAM,
                $pageSize
            );
        }

        return $uri;
    }

    public function setStoreId(int $storeId): void
    {
        $this->storeId = $storeId;
    }
}
