<?php

declare(strict_types=1);

namespace InPost\InPostPay\Logger;

use InPost\InPostPay\Provider\Config\DebugConfigProvider;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Monolog\LogRecord;

class Handler extends Base
{
    public const VAR_LOG_PATH = 'var/log';
    public const INPOST_PAY_LOG_CATALOG = 'inpost-pay';

    private ?string $logId = null;

    public function __construct(
        DriverInterface $filesystem,
        ?string $filePath = null,
        ?string $fileName = null,
        private readonly ?DebugConfigProvider $debugConfigProvider = null
    ) {
        $this->fileName = sprintf(
            '%s/%s/%s.log',
            self::VAR_LOG_PATH,
            self::INPOST_PAY_LOG_CATALOG,
            date('Y-m-d')
        );
        parent::__construct($filesystem, $filePath, $fileName);
        $this->bubble = false;
    }

    public function isHandling($record): bool
    {
        $minLogLevel = ($this->debugConfigProvider) ? $this->debugConfigProvider->getMinLogLevel() : $this->level;

        return (int)$record['level'] >= $minLogLevel;
    }

    public function handle($record): bool
    {
        if ($record instanceof LogRecord && method_exists($record, 'with')) { //@phpstan-ignore-line
            $recordData = $record->toArray();
            $recordMessage = (string)$recordData['message'];
            $record = $record->with(message: sprintf('[%s] %s', $this->getLogId(), $recordMessage));
        } elseif (is_array($record)) { //@phpstan-ignore-line
            $recordMessage = (string)$record['message'];
            $record['message'] = sprintf('[%s] %s', $this->getLogId(), $recordMessage);
        }

        return parent::handle($record);
    }

    /**
     * @return string
     */
    private function getLogId(): string
    {
        if ($this->logId === null) {
            $this->logId = uniqid();
        }

        return (string)$this->logId;
    }
}
