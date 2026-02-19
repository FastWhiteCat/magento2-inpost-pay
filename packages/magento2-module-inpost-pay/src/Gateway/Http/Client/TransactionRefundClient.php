<?php

declare(strict_types=1);

namespace InPost\InPostPay\Gateway\Http\Client;

use Exception;
use InPost\InPostPay\Api\Data\Merchant\RefundInterface;
use InPost\InPostPay\Service\ApiConnector\TransactionRefund;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class TransactionRefundClient implements ClientInterface
{
    public function __construct(
        private readonly TransactionRefund $refund
    ) {
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject): array
    {
        $body = $transferObject->getBody();

        try {
            $refundData = $body['refund_request_data'] ?? [];

            if (empty($refundData)) {
                return [];
            }

            $response['body'] = $this->refund->execute(
                $refundData[RefundInterface::TRANSACTION_ID] ?? null,
                $refundData[RefundInterface::EXTERNAL_REFUND_ID] ?? null,
                $refundData[RefundInterface::ADDITIONAL_BUSINESS_DATA] ?? null,
                $refundData[RefundInterface::REFUND_AMOUNT] ?? null,
                $refundData['store_id'] ?? null,
            );
        } catch (Exception $e) {
            $response = ['error' => $e->getMessage()];
        }

        return $response;
    }
}
