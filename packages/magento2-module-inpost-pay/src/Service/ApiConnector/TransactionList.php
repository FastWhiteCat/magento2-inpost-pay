<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Model\IziApi\Request\TransactionListRequest;
use InPost\InPostPay\Model\IziApi\Request\TransactionListRequestFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class TransactionList
{
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly TransactionListRequestFactory $transactionListRequestFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(
        int $perPage = 50,
        int $page = 0,
        ?string $sortDirection = 'ASC',
        ?string $sortBy = null,
        ?string $orderId = null,
        ?string $transactionId = null,
        ?string $merchantPosId = null,
        ?float $amountFrom = null,
        ?float $amountTo = null,
        ?string $currency = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        array $paymentMethod = [],
        array $status = []
    ): array {
        /** @var TransactionListRequest $request */
        $request = $this->transactionListRequestFactory->create();

        $params = [
            'page' => $page,
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_direction' => $sortDirection,
            'order_id' => $orderId,
            'transaction_id' => $transactionId,
            'merchant_pos_id' => $merchantPosId,
            'amount_from' => $amountFrom,
            'amount_to' => $amountTo,
            'currency' => $currency,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'paymentMethod' => $paymentMethod,
            'status' => $status
        ];

        $request->setParams(array_filter($params));

        try {
            return $this->connector->sendRequest($request);
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with get Transaction List. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }
}
