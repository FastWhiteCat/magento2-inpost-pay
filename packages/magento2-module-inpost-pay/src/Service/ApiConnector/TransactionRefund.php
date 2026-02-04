<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Api\Data\Merchant\Refund\AdditionalBusinessDataInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\RefundInterface;
use InPost\InPostPay\Api\Data\Merchant\RefundInterfaceFactory;
use InPost\InPostPay\Enum\InPostRefundStatus;
use InPost\InPostPay\Model\IziApi\Request\TransactionRefundRequest;
use InPost\InPostPay\Model\IziApi\Request\TransactionRefundRequestFactory;
use InPost\InPostPay\Model\IziApi\Response\TransactionRefundResponse;
use InPost\InPostPay\Model\IziApi\Response\TransactionRefundResponseFactory;
use InPost\InPostPay\Service\ApiConnector\Refund\ApiRefundTransactionProvider;
use InPost\InPostPay\Service\Converter\InPostRefundToArrayConverter;
use InPost\InPostPay\Service\Refund\SignatureGenerator;
use InPost\InPostPay\Service\UuidGenerator;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransactionRefund
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly AdditionalBusinessDataInterfaceFactory $additionalBusinessDataFactory,
        private readonly ConnectorInterface $connector,
        private readonly RefundInterfaceFactory $refundFactory,
        private readonly SignatureGenerator $signatureGenerator,
        private readonly TransactionRefundRequestFactory $transactionRefundRequestFactory,
        private readonly TransactionRefundResponseFactory $transactionRefundResponseFactory,
        private readonly InPostRefundToArrayConverter $inPostRefundToArrayConverter,
        private readonly ApiRefundTransactionProvider $apiRefundTransactionProvider,
        private readonly UuidGenerator $uuidGenerator,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(
        ?string $transactionId = null,
        ?string $refundId = null,
        ?string $refundAdditionalInfo = null,
        ?float $refundAmount = null,
        ?int $storeId = null
    ): TransactionRefundResponse {
        /** @var TransactionRefundRequest $request */
        $request = $this->transactionRefundRequestFactory->create();

        $refund = $this->refundFactory->create();
        $this->processAdditionalBusinessData($refund, $refundAdditionalInfo);

        $refund->setXCommandId($this->uuidGenerator->uuidv4());
        $refund->setTransactionId($transactionId);
        $refund->setExternalRefundId($refundId);
        $refund->setRefundAmount($refundAmount);
        $refund->setSignature($this->signatureGenerator->generate($refund, $storeId));

        $refundParams = $this->inPostRefundToArrayConverter->convert($refund);

        if ($storeId) {
            $request->setStoreId($storeId);
        }

        $request->setParams($refundParams);

        try {
            $result = $this->connector->sendRequest($request);

            return $this->handleSuccessfulRefund($result);
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with creating Refund transaction. Details: %1', $e->getMessage());

            if (in_array($e->getCode(), [Response::HTTP_BAD_GATEWAY, Response::HTTP_INTERNAL_SERVER_ERROR])) {
                return $this->handleErrorRefund($refund, $errorMsg->render(), $e->getCode());
            } else {
                $this->logger->critical($errorMsg->render());

                throw new LocalizedException($errorMsg, $e, $e->getCode());
            }
        }
    }

    private function handleSuccessfulRefund(array $result): TransactionRefundResponse
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

    private function handleErrorRefund(
        RefundInterface $refund,
        string $errorMsg,
        int $errorCode
    ): TransactionRefundResponse {
        $externalRefundId = $refund->getExternalRefundId() ?? '';
        try {
            $refundTransactionData = $this->apiRefundTransactionProvider->getRefundTransactionDataByTransactionId(
                (string)$refund->getTransactionId(),
                $externalRefundId,
                true
            );
        } catch (LocalizedException $e) {
            throw new LocalizedException(__('%1 API Error: %2', $e->getMessage(), $errorMsg), $e, $errorCode);
        }
        $externalRefundId = $refundTransactionData[TransactionRefundResponse::EXTERNAL_REFUND_ID] ?? $externalRefundId;
        $status = $refundTransactionData[TransactionRefundResponse::STATUS] ?? InPostRefundStatus::PENDING->value;
        $description = $refundTransactionData[TransactionRefundResponse::DESCRIPTION] ?? '';

        if ($description) {
            $description = __('%1 API Error: %2', $description, $errorMsg)->render();
        } else {
            $description = __('API Error: %1', $errorMsg)->render();
        }

        $refundAmount = $refundTransactionData[TransactionRefundResponse::REFUND_AMOUNT] ?? $refund->getRefundAmount();

        /** @var TransactionRefundResponse $transactionRefundResponse */
        $transactionRefundResponse = $this->transactionRefundResponseFactory->create();
        $transactionRefundResponse->setExternalRefundId($externalRefundId);
        $transactionRefundResponse->setStatus($status);
        $transactionRefundResponse->setDescription($description);
        $transactionRefundResponse->setRefundAmount($refundAmount);

        return $transactionRefundResponse;
    }

    private function processAdditionalBusinessData(
        RefundInterface $refund,
        ?string $refundAdditionalInfo
    ): void {
        if (!$refundAdditionalInfo) {
            return;
        }

        $additionalBusinessData = $this->additionalBusinessDataFactory->create();
        $additionalBusinessData->setAdditionalData($refundAdditionalInfo);
        $refund->setAdditionalBusinessData($additionalBusinessData);
    }
}
