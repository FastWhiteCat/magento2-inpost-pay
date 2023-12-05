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
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class Order implements OrderInterface
{
    public function __construct(
        private readonly RestRequest $restRequest,
        private readonly JsonSerializer $jsonSerializer,
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
            $requestParams = $this->jsonSerializer->unserialize((string)$this->restRequest->getContent());
            $dtoOrder = $this->dtoOrderFactory->create($requestParams);
            $inPostPayQuote = $this->inPostPayQuoteRepository->getByBasketId($dtoOrder->getOrderDetails()->getBasketId());
            $quote = $this->cartRepository->get($inPostPayQuote->getQuoteId());
            if ($quote instanceof Quote && $quote->getId()) {
                $this->orderValidator->validate($quote, $inPostPayQuote, $dtoOrder);
                $order = $this->orderProcessor->execute($quote, $dtoOrder);

                return $this->orderToInPostOrderConverter->convert($order);
            } else {
                throw new LocalizedException(__('Quote not found.'));
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw new LocalizedException(__('Order could not be created. Reason: %1', $e->getMessage()));
        }
    }
}
