<?php
declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use Psr\Log\LoggerInterface;

class OrderProcessingStep
{
    protected string $stepCode = '';

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function getStepCode(): string
    {
        return (string)$this->stepCode;
    }

    public function setStepCode(string $stepCode): void
    {
        $this->stepCode = $stepCode;
    }

    protected function createLog(string $message, array $data = [], bool $isError = false): void
    {
        if ($isError) {
            $this->logger->error(sprintf('%s[error]: %s', $this->getStepCode(), $message), $data);
        } else {
            $this->logger->debug(sprintf('%s[debug]: %s', $this->getStepCode(), $message), $data);
        }
    }
}
