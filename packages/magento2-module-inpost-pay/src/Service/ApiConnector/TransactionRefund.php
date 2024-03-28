<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Model\Data\Merchant\Refund\AdditionalBusinessData;
use InPost\InPostPay\Model\IziApi\Request\TransactionRefundRequest;
use InPost\InPostPay\Model\IziApi\Request\TransactionRefundRequestFactory;
use InPost\InPostPay\Model\IziApi\Response\TransactionRefundResponse;
use InPost\InPostPay\Model\IziApi\Response\TransactionRefundResponseFactory;
use InPost\InPostPay\Service\Converter\InPostRefundAdditionalDataToArrayConverter;
use InPost\InPostPay\Service\Refund\SignatureGenerator;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class TransactionRefund
{
    public function __construct(
        private readonly AdditionalBusinessData $additionalBusinessData,
        private readonly ConnectorInterface $connector,
        private readonly SignatureGenerator $signatureGenerator,
        private readonly TransactionRefundRequestFactory $transactionRefundRequestFactory,
        private readonly TransactionRefundResponseFactory $transactionRefundResponseFactory,
        private readonly InPostRefundAdditionalDataToArrayConverter $inPostRefundAdditionalDataToArrayConverter,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(
        string $transactionId,
        array $requestData = []
    ): TransactionRefundResponse {
        $request = $this->transactionRefundRequestFactory->create();

        $xCommandId = uniqid('', true);
        $additionalDataObject = $this->additionalBusinessData->setAdditionalData(null);
        $additionalData = $this->inPostRefundAdditionalDataToArrayConverter->convert($additionalDataObject);
        $refundId = $requestData['refund_id'] ?? '';
        $refundAmount = (float)($requestData['refund_amount'] ?? 0);

        $params = [
            TransactionRefundRequest::X_COMMAND_ID => $xCommandId,
            TransactionRefundRequest::TRANSACTION_ID => $transactionId,
            TransactionRefundRequest::EXTERNAL_REFUND_ID => $refundId,
            TransactionRefundRequest::REFUND_AMOUNT => $refundAmount,
            TransactionRefundRequest::ADDITIONAL_BUSINESS_DATA => $additionalData
        ];

        $params[TransactionRefundRequest::SIGNATURE] = $this->signatureGenerator
            ->generate($xCommandId, $transactionId, $params);

        $request->setParams($params);

        try {
            $result = $this->connector->sendRequest($request);

            return $this->handle($result);
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with creating Refund transaction. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }

    private function handle(array $result): TransactionRefundResponse
    {
        $externalRefundId = $result[TransactionRefundResponse::EXTERNAL_REFUND_ID] ?? '';
        $status = $result[TransactionRefundResponse::STATUS] ?? '';
        $description = $result[TransactionRefundResponse::DESCRIPTION] ?? '';
        $refundAmount = $result[TransactionRefundResponse::REFUND_AMOUNT] ?? 0.00;

        /** @var TransactionRefundResponse $transactionRefundResponse */
        $transactionRefundResponse = $this->transactionRefundResponseFactory->create();
        $transactionRefundResponse->setExternalRefundId($externalRefundId);
        $transactionRefundResponse->setStatus($status);
        $transactionRefundResponse->setDescription($description);
        $transactionRefundResponse->setRefundAmount($refundAmount);

        return $transactionRefundResponse;
    }
}
