<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Model\IziApi\Request\BasketBindingRequest;
use InPost\InPostPay\Model\IziApi\Request\BasketBindingRequestFactory;
use InPost\InPostPay\Model\IziApi\Response\BasketInformationResponse;
use InPost\InPostPay\Model\IziApi\Response\BasketInformationResponseFactory;
use InPost\InPostPay\Service\GetBasketId;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class BasketBindingCreate
{
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly BasketBindingRequestFactory $basketBindingRequestFactory,
        private readonly BasketInformationResponseFactory $basketInformationResponseFactory,
        private readonly GetBasketId $getBasketId,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(
        int $quoteId,
        string $bindingPlace,
        array $browser,
        ?string $prefix = null,
        ?string $phoneNumber = null
    ): BasketInformationResponse {
        $basketId = $this->getBasketId->get($quoteId, true);

        /** @var BasketBindingRequest $request */
        $request = $this->basketBindingRequestFactory->create();
        $params = [
            'basket_id' => $basketId,
            "binding_place" => $bindingPlace,
            "browser" => $browser
        ];

        if ($prefix && $phoneNumber) {
            $params['phone_number'] = [
                'country_prefix' => $prefix,
                'phone' => $phoneNumber,
            ];
            $params['binding_method'] = 'PHONE';
        } else {
            $params['binding_method'] = 'DEEP_LINK';
        }

        $request->setParams($params);

        try {
            $result = $this->connector->sendRequest($request);
            $result['basket_id'] = $basketId;

            return $this->handle($result);
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with binding basket. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }

    private function handle(array $result): BasketInformationResponse
    {
        $basketId = $result[BasketInformationResponse::BASKET_ID] ?? '';
        $qrCode = $result[BasketInformationResponse::QR_CODE] ?? null;
        $deepLink = $result[BasketInformationResponse::DEEP_LINK] ?? null;
        $deepLinkHms = $result[BasketInformationResponse::DEEP_LINK_HMS] ?? null;

        /** @var BasketInformationResponse $basketInformationResponse */
        $basketInformationResponse = $this->basketInformationResponseFactory->create();
        $basketInformationResponse->setBasketId($basketId);
        if ($qrCode) {
            $basketInformationResponse->setQrCode($qrCode);
            $basketInformationResponse->setDeepLink($deepLink);
            $basketInformationResponse->setDeepLinkHms($deepLinkHms);
        }

        return $basketInformationResponse;
    }
}
