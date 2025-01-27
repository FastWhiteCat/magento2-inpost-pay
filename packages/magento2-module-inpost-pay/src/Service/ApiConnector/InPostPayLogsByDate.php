<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use InPost\InPostPay\Api\ApiConnector\InPostPayLogsByDateInterface;
use InPost\InPostPay\Api\Data\Merchant\Logs\InPostPayLogInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Logs\InPostPayLogInterface;
use InPost\InPostPay\Model\Config\Source\LogLevel;
use InPost\InPostPay\Provider\Config\DebugConfigProvider;
use InPost\InPostPay\Provider\Logs\LogFileContentByDateProvider;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;

class InPostPayLogsByDate implements InPostPayLogsByDateInterface
{
    /**
     * @param InPostPayLogInterfaceFactory $inPostPayLogFactory
     * @param LogLevel $logLevel
     * @param DebugConfigProvider $debugConfigProvider
     * @param LogFileContentByDateProvider $logFileContentByDateProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly InPostPayLogInterfaceFactory $inPostPayLogFactory,
        private readonly LogLevel $logLevel,
        private readonly DebugConfigProvider $debugConfigProvider,
        private readonly LogFileContentByDateProvider $logFileContentByDateProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param string $logDate
     * @return \InPost\InPostPay\Api\Data\Merchant\Logs\InPostPayLogInterface
     * @throws LocalizedException
     */
    public function execute(string $logDate): InPostPayLogInterface
    {
        try {
            $this->validateLogDate($logDate);
            $logContent = $this->logFileContentByDateProvider->getContent($logDate);

            /** @var InPostPayLogInterface $inPostPayLog */
            $inPostPayLog = $this->inPostPayLogFactory->create();
            $inPostPayLog->setLogLevel($this->getLogLevelLabel());
            $inPostPayLog->setContent($logContent);

            $this->logger->info(
                sprintf('InPost Pay Logs were accessed by Magento REST Endpoint for date: %s', $logDate)
            );

            return $inPostPayLog;
        } catch (FileSystemException $e) {
            $this->logger->error(
                sprintf(
                    'InPost Pay Logs could not be accessed by Magento REST Endpoint for date: %s. Reason: %s',
                    $logDate,
                    $e->getMessage()
                )
            );

            throw new LocalizedException(__('Logs from this date are not accessible.'));
        }
    }

    /**
     * @return string
     */
    private function getLogLevelLabel(): string
    {
        $label = '';
        $minLogLevel = $this->debugConfigProvider->getMinLogLevel();
        $logLevelOptions = $this->logLevel->toOptionArray();

        foreach ($logLevelOptions as $logLevelOption) {
            $value = $logLevelOption['value'] ?? null;

            if ($value && $value === $minLogLevel) {
                $label = $logLevelOption['label'] ?? '';

                if ($label instanceof Phrase) {
                    $label = $label->render();
                }

                break;
            }
        }

        return is_scalar($label) ? (string)$label : '';
    }

    /**
     * @param string $logDate
     * @return void
     * @throws LocalizedException
     */
    private function validateLogDate(string $logDate): void
    {
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $logDate)) {
            throw new LocalizedException(__('Invalid log date! Use format YYYY-MM-DD.'));
        }
    }
}
