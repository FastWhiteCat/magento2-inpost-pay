<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\IziApi\Request;

use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Model\Request;
use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Service\ApiConnector\TokenGenerator;
use Laminas\Http\Request as HttpRequest;

class TransactionRefundRequest extends Request implements RequestInterface
{
    public const X_COMMAND_ID = 'X-Command-ID';
    public const X_COMMAND_ID_AS_PARAM = 'x_command_id';
    public const TRANSACTION_ID = 'transaction_id';
    public const EXTERNAL_REFUND_ID = 'external_refund_id';
    public const REFUND_AMOUNT = 'refund_amount';
    public const ADDITIONAL_BUSINESS_DATA = 'additional_business_data';
    public const SIGNATURE = 'signature';

    protected string $method = HttpRequest::METHOD_POST;
    protected ?string $contentType = 'application/json';
    protected string $uri = '/v1/izi/transaction/{transaction_id}/refund';

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
        if (array_key_exists(self::TRANSACTION_ID, $params) && is_scalar($params[self::TRANSACTION_ID])) {
            $transactionId = (string)$params[self::TRANSACTION_ID];
            $uri = str_replace(sprintf('{%s}', self::TRANSACTION_ID), $transactionId, $uri);
            if (!$keepParamsIntact) {
                unset($params[self::TRANSACTION_ID]);
                $this->setParams($params);
            }
        }

        return $uri;
    }

    public function getHeaders(bool $keepParamsIntact = false): array
    {
        $headers = parent::getHeaders($keepParamsIntact);

        $params = $this->getParams();
        if (array_key_exists(self::X_COMMAND_ID_AS_PARAM, $params) && is_scalar($params[self::X_COMMAND_ID_AS_PARAM])) {
            $headers[self::X_COMMAND_ID] = (string)$params[self::X_COMMAND_ID_AS_PARAM];
            if (!$keepParamsIntact) {
                unset($params[self::X_COMMAND_ID_AS_PARAM]);
                $this->setParams($params);
            }
        }

        return $headers;
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
