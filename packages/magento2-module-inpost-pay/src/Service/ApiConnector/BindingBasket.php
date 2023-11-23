<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Model\IziApi\Request\PublicKeyRequest;
use InPost\InPostPay\Model\IziApi\Request\BasketBindingRequestFactory;
use InPost\InPostPay\Model\IziApi\Request\BasketBindingVerifyRequestFactory;
use InPost\InPostPay\Service\GetBasketId;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class BindingBasket
{
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly BasketBindingRequestFactory $basketBindingRequestFactory,
        private readonly BasketBindingVerifyRequestFactory $basketBindingVerifyRequest,
        private readonly GetBasketId $getBasketId,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param int $quoteId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkBinding(int $quoteId): array
    {
        $basketId = $this->getBasketId->get($quoteId);

        if (!$basketId) {
            return ['browser_trusted' => false, 'basket_linked' => false];
        }

        /** @var PublicKeyRequest $request */
        $request = $this->basketBindingVerifyRequest->create();

        $request->setParams([
            'basket_id' => $basketId,
        ]);

        try {
            return $this->connector->sendRequest($request);
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with binding checking. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }

    public function bindBasket(
        int $quoteId,
        string $bindingPlace,
        array $browser,
        ?string $prefix = null,
        ?string $phoneNumber = null
    ): array {
        $basketId = $this->getBasketId->get($quoteId, true);

        /** @var PublicKeyRequest $request */
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

            return [$result];
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with binding basket. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }
}
