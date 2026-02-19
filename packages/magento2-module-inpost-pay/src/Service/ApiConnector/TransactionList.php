<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Model\IziApi\Request\TransactionListRequest;
use InPost\InPostPay\Model\IziApi\Request\TransactionListRequestFactory;
use InPost\InPostPay\Model\IziApi\Response\TransactionListResponse;
use InPost\InPostPay\Service\DataTransfer\TransactionListResponseDataTransfer;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class TransactionList
{
    private const DEFAULT_PER_PAGE_VALUE = 10;

    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly TransactionListRequestFactory $transactionListRequestFactory,
        private readonly TransactionListResponseDataTransfer $transactionListResponseDataTransfer,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function execute(
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
        array $status = [],
        ?int $storeId = null
    ): TransactionListResponse {
        /** @var TransactionListRequest $request */
        $request = $this->transactionListRequestFactory->create();

        if ($storeId) {
            $request->setStoreId($storeId);
        }

        $params = [
            'page' => 0,
            'per_page' => self::DEFAULT_PER_PAGE_VALUE,
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
            $count = 0;
            $transactionItems = [];

            while ($response = $this->connector->sendRequest($request)) {
                $count = $response[TransactionListResponse::COUNT] ?? 0;
                $page = $response[TransactionListResponse::PAGE] ?? 0;
                $perPage = $response[TransactionListResponse::PER_PAGE] ?? 0;
                $transactionItems[] = $response[TransactionListResponse::ITEMS] ?? [];

                if ($perPage < self::DEFAULT_PER_PAGE_VALUE) {
                    break;
                }

                $params[TransactionListResponse::PAGE] = $page + 1;
                $request->setParams(array_filter($params));
            }

            $result = [
                TransactionListResponse::COUNT => $count,
                TransactionListResponse::ITEMS => array_merge(...$transactionItems)
            ];

            return $this->handle($result);
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with get Transaction List. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }

    private function handle(array $result): TransactionListResponse
    {
        return $this->transactionListResponseDataTransfer->convertToObject($result);
    }
}
