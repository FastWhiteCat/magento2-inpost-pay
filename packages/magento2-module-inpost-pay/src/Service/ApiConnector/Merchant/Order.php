<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Api\OrderProcessorInterface;
use InPost\InPostPay\Model\Dto\DtoOrderFactory;
use InPost\InPostPay\Service\Converter\OrderToInPostOrderConverter;
use InPost\InPostPay\Validator\OrderValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Serialize\Serializer\Base64Json as Base64JsonSerializer;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class Order implements OrderInterface
{
    private const REQUEST_PREFIX = 'ORDER_REQUEST';
    private const RESPONSE_PREFIX = 'ORDER_RESPONSE';

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly RestRequest $restRequest,
        private readonly JsonSerializer $jsonSerializer,
        private readonly Base64JsonSerializer $base64JsonSerializer,
        private readonly DtoOrderFactory $dtoOrderFactory,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly OrderValidator $orderValidator,
        private readonly OrderProcessorInterface $orderProcessor,
        private readonly OrderToInPostOrderConverter $orderToInPostOrderConverter,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function create(): array
    {
        try {
            $requestParams = (array)$this->jsonSerializer->unserialize((string)$this->restRequest->getContent());
            $this->createRequestDebugLog(self::REQUEST_PREFIX,  'Order Create', $requestParams);
            $dtoOrder = $this->dtoOrderFactory->create($requestParams);
            // @phpstan-ignore-next-line
            $basketId = $dtoOrder->getOrderDetails()->getBasketId();
            $inPostPayQuote = $this->inPostPayQuoteRepository->getByBasketId($basketId);
            $quote = $this->cartRepository->get($inPostPayQuote->getQuoteId());
            if ($quote instanceof Quote && $quote->getId()) {
                // @phpstan-ignore-next-line
                $this->orderValidator->validate($quote, $inPostPayQuote, $dtoOrder);
                // @phpstan-ignore-next-line
                $order = $this->orderProcessor->execute($quote, $dtoOrder);
                $orderData = $this->orderToInPostOrderConverter->convert($order);
                $this->createRequestDebugLog(self::RESPONSE_PREFIX, 'Order Create', $orderData);

                return $orderData;
            } else {
                throw new LocalizedException(__('Quote not found.'));
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw new LocalizedException(__('Order could not be created. Reason: %1', $e->getMessage()));
        }
    }

    private function createRequestDebugLog(string $logPrefix, string $message, array $data = []): void
    {
        $serializedData = ($data) ? $this->base64JsonSerializer->serialize($data) : '';
        $dataLabel = ($logPrefix === self::REQUEST_PREFIX) ? 'Payload' : 'Response';
        $this->logger->debug(sprintf('%s: %s %s: %s', $logPrefix, $message, $dataLabel, $serializedData));
    }
}
