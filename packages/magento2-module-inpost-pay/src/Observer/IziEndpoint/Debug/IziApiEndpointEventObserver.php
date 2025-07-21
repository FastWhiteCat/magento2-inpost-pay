<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\IziEndpoint\Debug;

use InPost\InPostPay\Provider\Config\DebugConfigProvider;
use InPost\InPostPay\Traits\AnonymizerTrait;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Monolog\Logger;

class IziApiEndpointEventObserver
{
    use AnonymizerTrait;

    protected string $eventDescription = 'SENDING: IZI API Event';

    public function __construct(
        protected readonly ExtensibleDataObjectConverter $objectConverter,
        protected readonly DebugConfigProvider $debugConfigProvider,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
    ) {
    }

    protected function canDebug(): bool
    {
        return $this->debugConfigProvider->getMinLogLevel() <= Logger::DEBUG;
    }

    protected function createEventDataLog(array $eventData): void
    {
        $this->logger->debug(
            sprintf('%s. Context: %s', $this->eventDescription, $this->serializer->serialize($eventData))
        );
    }
}
