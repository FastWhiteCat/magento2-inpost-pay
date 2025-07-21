<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Analytics\Event\Purchase;

use InPost\InPostPay\Api\Order\Analytics\Event\Purchase\EventDataHandlerInterface;
use InPost\InPostPay\Exception\UnableToSendInPostPayAnalyticsDataException;
use Exception;
use GuzzleHttp\Client;
use InPost\InPostPay\Provider\Config\AnalyticsConfigProvider;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response as GuzzleHttpResponse;
use Laminas\Http\Response;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;

class Ga4PurchaseEventHandler implements EventDataHandlerInterface
{
    public const EVENT_CODE = 'ga4_purchase_event';
    public const GA_ANALYTICS_PURCHASE_EVENT_REQUEST = 'ga_analytics_purchase_event_request';
    public const GA_API_SECRET_URL_PARAM = 'api_secret';
    public const GA_API_MEASUREMENT_ID_URL_PARAM = 'measurement_id';
    public const HEADER = 'header';
    public const URL = 'url';
    public const PARAMS = 'params';
    public const METHOD = 'method';

    public function __construct(
        private readonly AnalyticsConfigProvider $analyticsConfigProvider,
        private readonly ClientFactory $clientFactory,
        private readonly EventManager $eventManager,
        private readonly Json $serializer,
        private readonly LoggerInterface $logger
    ) {
    }

    public function getEventCode(): string
    {
        return self::EVENT_CODE;
    }

    /**
     * @param array $eventData
     * @param int $storeId
     * @return void
     * @throws UnableToSendInPostPayAnalyticsDataException
     */
    public function send(array $eventData, int $storeId): void
    {
        $measurementId = $this->analyticsConfigProvider->getGaMeasurementId($storeId);
        $gaApiSecret = $this->analyticsConfigProvider->getGaApiSecret($storeId);
        $gaApiUrl = $this->analyticsConfigProvider->getGaApiUrl();

        if (!$this->analyticsConfigProvider->isAnalyticsEnabled($storeId)
            || empty($measurementId)
            || empty($gaApiSecret)
            || empty($gaApiUrl)
        ) {
            return;
        }

        $gaApiUrl = $this->prepareGaApiEndpointUrl($gaApiUrl, $measurementId, $gaApiSecret);
        $headers = [
            'Content-Type' => 'application/json'
        ];

        $gaAnalyticsPurchaseEventRequestDataObject = new DataObject();
        $gaAnalyticsPurchaseEventRequestDataObject->setData(
            self::HEADER,
            $headers
        );
        $gaAnalyticsPurchaseEventRequestDataObject->setData(
            self::URL,
            $gaApiUrl
        );
        $gaAnalyticsPurchaseEventRequestDataObject->setData(
            self::PARAMS,
            $eventData
        );
        $gaAnalyticsPurchaseEventRequestDataObject->setData(
            self::METHOD,
            'POST'
        );

        $this->eventManager->dispatch(
            'ga_analytics_purchase_event_request_send_before',
            [
                self::GA_ANALYTICS_PURCHASE_EVENT_REQUEST => $gaAnalyticsPurchaseEventRequestDataObject
            ]
        );

        $url = $gaAnalyticsPurchaseEventRequestDataObject->getData(self::URL);
        $headers = $gaAnalyticsPurchaseEventRequestDataObject->getData(self::HEADER);
        $params = $gaAnalyticsPurchaseEventRequestDataObject->getData(self::PARAMS);
        $method = $gaAnalyticsPurchaseEventRequestDataObject->getData(self::METHOD);

        if (!is_string($url)) {
            throw new UnableToSendInPostPayAnalyticsDataException(
                __('Google Analytics API endpoint URL is invalid: %1', $url)
            );
        }

        if (!is_array($headers)) {
            throw new UnableToSendInPostPayAnalyticsDataException(
                __('Google Analytics API request headers are invalid!')
            );
        }

        if (!is_array($params)) {
            throw new UnableToSendInPostPayAnalyticsDataException(
                __('Google Analytics API request params are invalid!')
            );
        }

        if (!is_string($method)) {
            throw new UnableToSendInPostPayAnalyticsDataException(
                __('Google Analytics API request method are invalid: %1', $method)
            );
        }

        $this->sendRequest($url, $headers, $method, $params);
    }

    /**
     * @param string $url
     * @param array $headers
     * @param string $method
     * @param array $params
     * @return void
     * @throws UnableToSendInPostPayAnalyticsDataException
     */
    public function sendRequest(string $url, array $headers, string $method, array $params): void
    {
        $this->logger->debug(
            'SENDING Google Analytics API REQUEST',
            [
                'method' => $method,
                'url' => $url,
                'headers' => $headers,
                'params' => $params
            ]
        );

        $client = $this->getClient($headers);

        try {
            $requestParams = (!empty($params)) ? ['json' => $params]: [];
            $response = $client->{$method}($url, $requestParams);
        } catch (Exception $e) {
            $errorMsg = sprintf(
                'Google Analytics API endpoint "%s" responded[%s] with an error: %s',
                $url,
                $e->getCode(),
                $e->getMessage()
            );
            $this->logger->critical($errorMsg);

            throw new UnableToSendInPostPayAnalyticsDataException(__($errorMsg));
        }

        $response = $this->handleResponse($response, $url);
        $this->logger->debug('SENDING Google Analytics API RESPONSE', $response);
    }

    /**
     * @param array $headers
     * @return Client
     */
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

    /**
     * @param GuzzleHttpResponse $response
     * @param string $url
     * @return array
     * @throws UnableToSendInPostPayAnalyticsDataException
     */
    private function handleResponse(GuzzleHttpResponse $response, string $url): array
    {
        $responseBody = (string)$response->getBody()->getContents();
        $statusCode = (int)$response->getStatusCode();

        if ($statusCode !== Response::STATUS_CODE_200
            && $statusCode !== Response::STATUS_CODE_201
            && $statusCode !== Response::STATUS_CODE_202
            && $statusCode !== Response::STATUS_CODE_204
        ) {
            $this->logger->critical(
                sprintf('Google Analytics API Response:%s. Content: %s', $statusCode, $responseBody)
            );

            throw new UnableToSendInPostPayAnalyticsDataException(
                __('Google Analytics API endpoint "%1" responded with %2 code.', $url, $statusCode)
            );
        }

        $result = $responseBody ? $this->serializer->unserialize($responseBody) : [];

        return [
            'code' => $statusCode,
            'content' => (is_scalar($result) || is_array($result)) ? $result : 'unknown',
        ];
    }

    /**
     * @param string $gaApiUrl
     * @param string $measurementId
     * @param string $gaApiSecret
     * @return string
     */
    private function prepareGaApiEndpointUrl(string $gaApiUrl, string $measurementId, string $gaApiSecret): string
    {
        $params = [
            self::GA_API_SECRET_URL_PARAM => $gaApiSecret,
            self::GA_API_MEASUREMENT_ID_URL_PARAM => $measurementId,
        ];

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return $gaApiUrl . (parse_url($gaApiUrl, PHP_URL_QUERY) ? '&' : '?') . http_build_query($params);
    }
}
