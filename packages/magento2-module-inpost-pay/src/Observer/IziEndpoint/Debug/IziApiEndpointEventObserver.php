<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\IziEndpoint\Debug;

use InPost\InPostPay\Provider\Config\DebugConfigProvider;
use Psr\Log\LoggerInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Monolog\Logger;

class IziApiEndpointEventObserver
{
    protected string $eventDescription = 'SENDING: IZI API Event';

    public function __construct(
        protected readonly ExtensibleDataObjectConverter $objectConverter,
        private readonly DebugConfigProvider $debugConfigProvider,
        private readonly LoggerInterface $logger,
    ) {
    }

    protected function canDebug(): bool
    {
        return $this->debugConfigProvider->getMinLogLevel() <= Logger::DEBUG;
    }

    protected function createEventDataLog(array $eventData): void
    {
        $this->logger->debug($this->eventDescription, $eventData);
    }
}
