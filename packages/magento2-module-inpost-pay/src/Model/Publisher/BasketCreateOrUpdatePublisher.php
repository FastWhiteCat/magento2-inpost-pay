<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Publisher;

use Exception;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Psr\Log\LoggerInterface;

class BasketCreateOrUpdatePublisher
{
    public const TOPIC_NAME = 'inpost_pay.basket.create_or_update';

    public function __construct(
        private readonly OperationInterfaceFactory $operationFactory,
        private readonly PublisherInterface $publisher,
        private readonly JsonSerializer $jsonSerializer,
        private readonly LoggerInterface $logger
    ) {
    }

    public function publish(InPostPayQuoteInterface $inPostPayQuote): void
    {
        $basketData = [InPostPayQuoteInterface::BASKET_ID => $inPostPayQuote->getBasketId()];
        try {
            /** @var OperationInterface $operation */
            $operation = $this->operationFactory->create();
            $operation->setStatus(OperationInterface::STATUS_TYPE_OPEN);
            $operation->setTopicName(self::TOPIC_NAME);
            $serializedBasketData = $this->jsonSerializer->serialize($basketData);
            if (is_string($serializedBasketData)) {
                $operation->setSerializedData($serializedBasketData);
            } else {
                throw new LocalizedException(__('Unable to serialize basket data.'));
            }
            $this->publisher->publish(self::TOPIC_NAME, $operation);
            $this->logger->debug(sprintf('Message added to queue! TOPIC: %s', self::TOPIC_NAME), $basketData);
        } catch (Exception $e) {
            $this->logger->error(
                sprintf(
                    'Message could not be added to queue! TOPIC: %s. Reason: %s',
                    self::TOPIC_NAME,
                    $e->getMessage()
                ),
                $basketData
            );
        }
    }
}
