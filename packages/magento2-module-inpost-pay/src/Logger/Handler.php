<?php

declare(strict_types=1);

namespace InPost\InPostPay\Logger;

use InPost\InPostPay\Provider\Config\DebugConfigProvider;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    public const LOG_FILE_PATH = 'var/log/inpost-pay';

    private ?string $logId = null;

    public function __construct(
        DriverInterface $filesystem,
        ?string $filePath = null,
        ?string $fileName = null,
        private readonly ?DebugConfigProvider $debugConfigProvider = null
    ) {
        $this->fileName = sprintf('%s/%s.log', self::LOG_FILE_PATH, date('Y-m-d'));
        parent::__construct($filesystem, $filePath, $fileName);
        $this->bubble = false;
    }

    public function isHandling(array $record): bool
    {
        $minLogLevel = ($this->debugConfigProvider) ? $this->debugConfigProvider->getMinLogLevel() : $this->level;

        return (int)$record['level'] >= $minLogLevel;
    }

    public function handle(array $record): bool
    {
        $recordMessage = (string)$record['message'];
        $record['message'] = sprintf('[%s] %s', $this->getLogId(), $recordMessage);

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
