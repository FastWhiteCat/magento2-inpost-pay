<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use GuzzleHttp\Client;
use Laminas\Http\Client as HttpClient;
use GuzzleHttp\ClientFactory;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use InPost\InPostPay\Exception\InPostPayInvalidConfigurationException;
use Laminas\Http\Response;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class Connector implements ConnectorInterface
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly Json $serializer,
        private readonly Base64Json $base64serializer,
        private readonly LoggerInterface $logger
    ) {
    }

    public function sendRequest(RequestInterface $request, array $headers = []): array
    {
        $headers = $this->getHeaders($request, $headers);
        $client = $this->getClient($headers);
        $url = $this->getEndpointUrl($request);
        $params = $request->getParams();

        try {
            switch ($request->getContentType()) {
                case (HttpClient::ENC_URLENCODED):
                    $requestParams = !empty($params) ? ['form_params' => $params] : [];
                    break;
                case (HttpClient::ENC_FORMDATA):
                    $requestParams = !empty($params) ? ['multipart' => $params] : [];
                    break;
                default:
                    $requestParams = ['json' => $params];
            }
            $this->createRequestLog($url, $headers, $requestParams);
            $response = $client->{$request->getMethod()}($url, $requestParams);
        } catch (Exception $e) {
            $errorMsg = __('InPost API endpoint "%1" responded with an error: %2', $url, $e->getMessage());
            $this->createResponseLog($errorMsg->render(), $e->getCode(), true);

            throw new LocalizedException($errorMsg);
        }

        $responseBody = (string)$response->getBody()->getContents();
        $statusCode = (int)$response->getStatusCode();
        if ($statusCode !== Response::STATUS_CODE_200) {
            $this->createResponseLog($responseBody, $statusCode, true);

            throw new LocalizedException(__('InPost API endpoint "%1" responded with %1 code.', $url, $statusCode));
        } else {
            $this->createResponseLog($responseBody, $statusCode);
        }

        $resultData = [];
        $result = $this->serializer->unserialize($responseBody);
        if (is_scalar($result)) {
            $resultData['result'] = (string)$result;
        } elseif (is_array($result)) {
            $resultData = $result;
        }

        return $resultData;
    }

    private function getClient(array $headers): Client
    {
        return $this->clientFactory->create(
            [
                'config' => [
                    'cookies' => false,
                    'headers' => $headers
                ]
            ]
        );
    }

    private function getEndpointUrl(RequestInterface $request): string
    {
        return sprintf('%s/%s', trim($request->getApiUrl(), '/'), trim($request->getUri(), '/'));
    }

    /**
     * @param RequestInterface $request
     * @param array $additionalHeaders
     * @return array
     * @throws InPostPayInvalidConfigurationException
     */
    private function getHeaders(RequestInterface $request, array $additionalHeaders): array
    {
        $headers = [];

        if ($request->getContentType()) {
            $headers[RequestInterface::CONTENT_TYPE] = $request->getContentType();
        }

        $bearer = $request->getBearerToken();
        if ($bearer) {
            $headers[RequestInterface::AUTHORIZATION] = sprintf(RequestInterface::BEARER_PATTERN, $bearer);
        }

        return array_merge($headers, $additionalHeaders);
    }

    private function createRequestLog(string $url, array $headers, array $params): void
    {
        $logData = [
            'headers' => $headers,
            'params' => $params
        ];

        $this->logger->debug(
            sprintf('API Request for endpoint: %s. Payload: %s', $url, $this->base64serializer->serialize($logData))
        );
    }

    private function createResponseLog(string $body, int $code, bool $critical = false): void
    {
        $logMessage = sprintf(
            'API Response:%s. Content: %s',
            $code,
            $this->base64serializer->serialize(['body' => $body])
        );

        if ($critical) {
            $this->logger->critical($logMessage);
        } else {
            $this->logger->debug($logMessage);
        }
    }
}
