<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use GuzzleHttp\Client;
use Laminas\Http\Client as HttpClient;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response as GuzzleHttpResponse;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Api\ApiConnector\RequestInterface;
use Laminas\Http\Response;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;

class Connector implements ConnectorInterface
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly EventManager $eventManager,
        private readonly Json $serializer,
        private readonly LoggerInterface $logger
    ) {
    }

    public function sendRequest(RequestInterface $request): array
    {
        $this->eventManager->dispatch('izi_api_send_request_before', [self::REQUEST => $request]);

        $headers = $request->getHeaders();
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
                    $requestParams = (!empty($params)) ? ['json' => $params]: [];
            }

            $response = $client->{$request->getMethod()}($url, $requestParams);
        } catch (Exception $e) {
            $errorMsg = sprintf(
                'InPost API endpoint "%s" responded[%s] with an error: %s',
                $url,
                $e->getCode(),
                $e->getMessage()
            );
            $this->logger->critical($errorMsg);
            $errorCode = (int)$e->getCode();

            if ($errorCode === 404) {
                throw new NotFoundException(__($errorMsg), null, $errorCode);
            }

            throw new LocalizedException(__($errorMsg), null, $errorCode);
        }

        $response = $this->handleResponse($response, $url);

        $this->eventManager->dispatch('izi_api_send_request_after', [self::RESPONSE => $response]);

        return $response;
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

    private function handleResponse(GuzzleHttpResponse $response, string $url): array
    {
        $responseBody = (string)$response->getBody()->getContents();
        $statusCode = (int)$response->getStatusCode();

        if ($statusCode !== Response::STATUS_CODE_200
            && $statusCode !== Response::STATUS_CODE_201
            && $statusCode !== Response::STATUS_CODE_202
            && $statusCode !== Response::STATUS_CODE_204
        ) {
            $this->logger->critical(sprintf('API Response:%s. Content: %s', $statusCode, $responseBody));

            throw new LocalizedException(__('InPost API endpoint "%1" responded with %2 code.', $url, $statusCode));
        }

        $resultData = [];
        $result = $responseBody ? $this->serializer->unserialize($responseBody) : [];
        if (is_scalar($result)) {
            $resultData['result'] = (string)$result;
        } elseif (is_array($result)) {
            $resultData = $result;
        }

        return $resultData;
    }
}
